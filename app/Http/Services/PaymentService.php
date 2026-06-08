<?php

namespace App\Http\Services;

use App\Models\Application;
use App\Models\Log;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PaymentService
{
    protected string $secretKey;
    protected string $publicKey;
    protected string $hmacSecret;
    protected array $paymentMethods;
    protected string $redirectUrl;
    protected string $baseUrl;

    public function __construct()
    {
        $this->secretKey      = config('services.paymob.secret_key');
        $this->publicKey      = config('services.paymob.public_key');
        $this->hmacSecret     = config('services.paymob.hmac_secret');
        $this->redirectUrl    = config('services.paymob.redirect_url');
        $this->baseUrl        = config('services.paymob.base_url');
        $this->paymentMethods = array_map(
            'intval',
            array_filter(explode(',', config('services.paymob.payment_methods', '')))
        );
    }

    // ─── Admin: Set Fee & Advance Stage ─────────────────────────────

    /**
     * Admin/manager sets the payment fee for an application.
     * - amount = 0  → skip payment, advance directly to 'approved'
     * - amount > 0  → create pending payment, advance to 'awaiting_payment'
     */
    public function setApplicationFee(Application $application, float $amount): array
    {
        return DB::transaction(function () use ($application, $amount) {
            if ($amount == 0) {
                $application->current_stage = 'approved';
                $application->save();

                // Log
                Log::create([
                    'application_id' => $application->id,
                    'user_id'        => $application->student_id,
                    'action'         => "Application [{$application->serial_number}] approved with zero fees",
                    'type'           => 'payment',
                ]);

                // Notify student
                Notification::create([
                    'user_id'        => $application->student_id,
                    'application_id' => $application->id,
                    'message'        => "تمت الموافقة على طلبك رقم {$application->serial_number} بدون رسوم.",
                    'channel'        => 'system',
                ]);

                return [
                    'skipped'     => true,
                    'application' => $application->fresh(),
                ];
            }

            // amount > 0: create pending payment record
            $payment = Payment::create([
                'application_id' => $application->id,
                'phase'          => 'initial',
                'amount'         => $amount,
                'provider'       => 'Paymob',
                'status'         => 'pending',
            ]);

            $application->current_stage = 'awaiting_payment';
            $application->save();

            // Log
            Log::create([
                'application_id' => $application->id,
                'user_id'        => $application->student_id,
                'action'         => "Payment of {$amount} EGP required for [{$application->serial_number}]",
                'type'           => 'payment',
            ]);

            // Notify student
            Notification::create([
                'user_id'        => $application->student_id,
                'application_id' => $application->id,
                'message'        => "مطلوب سداد رسوم بقيمة {$amount} ج.م لطلبك رقم {$application->serial_number}.",
                'channel'        => 'system',
            ]);

            return [
                'skipped'     => false,
                'application' => $application->fresh(),
                'payment'     => $payment,
            ];
        });
    }

    // ─── Paymob Intention ───────────────────────────────────────────

    /**
     * Create a Paymob payment intention and return the checkout URL.
     */
    public function createPaymentIntention(Application $application, User $user, Payment $payment): array
    {
        $reference = $application->serial_number . '-PAY-' . time();

        $nameParts = explode(' ', trim($user->full_name), 2);
        $firstName = $nameParts[0] ?? 'User';
        $lastName  = $nameParts[1] ?? 'Name';

        $amountCents = (int) ($payment->amount * 100);

        $payload = [
            'amount'          => $amountCents,
            'currency'        => 'EGP',
            'payment_methods' => $this->paymentMethods,
            'items'           => [
                [
                    'name'        => "رسوم الطلب رقم " . $application->serial_number,
                    'amount'      => $amountCents,
                    'description' => "رسوم مراجعة الطلب رقم " . $application->serial_number,
                    'quantity'    => 1,
                ],
            ],
            'billing_data' => [
                'first_name'   => $firstName,
                'last_name'    => $lastName,
                'phone_number' => $user->phone_number ?? 'NA',
                'email'        => $user->email ?? 'no-email@irb.edu',
                'street'       => 'NA',
                'building'     => 'NA',
                'floor'        => 'NA',
                'apartment'    => 'NA',
                'city'         => 'NA',
                'country'      => 'EG',
            ],
            'special_reference' => $reference,
            'redirection_url'   => $this->redirectUrl,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Token ' . $this->secretKey,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ])->withoutVerifying()->post($this->baseUrl, $payload);

        if ($response->successful()) {
            $data = $response->json();

            // Update payment record with the reference
            $payment->update(['transaction_reference' => $reference]);

            return [
                'success'           => true,
                'client_secret'     => $data['client_secret'],
                'special_reference' => $reference,
                'checkout_url'      => "https://accept.paymob.com/unifiedcheckout/?publicKey={$this->publicKey}&clientSecret={$data['client_secret']}",
            ];
        }

        return [
            'success' => false,
            'error'   => $response->json() ?: $response->body(),
        ];
    }

    // ─── Intention Details ──────────────────────────────────────────

    /**
     * Fetch payment intention details from Paymob (for frontend verification).
     */
    public function getIntentionDetails(string $clientSecret): array
    {
        $url = "https://accept.paymob.com/v1/intention/element/{$this->publicKey}/{$clientSecret}/";

        $response = Http::accept('application/json')->withoutVerifying()->get($url);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'success' => true,
                'data'    => $data,
                'is_paid' => $data['paid'] ?? false,
            ];
        }

        return [
            'success' => false,
            'error'   => $response->json(),
        ];
    }

    // ─── HMAC Verification ──────────────────────────────────────────

    /**
     * Verify the HMAC signature from Paymob's webhook callback.
     */
    public function verifyHmac(array $obj, string $receivedHmac): bool
    {
        $hmacFields = [
            'amount_cents'            => $obj['amount_cents'] ?? '',
            'created_at'              => $obj['created_at'] ?? '',
            'currency'                => $obj['currency'] ?? '',
            'error_occured'           => ($obj['error_occured'] ?? false) === true ? 'true' : 'false',
            'has_parent_transaction'  => ($obj['has_parent_transaction'] ?? false) === true ? 'true' : 'false',
            'id'                      => $obj['id'] ?? '',
            'integration_id'          => $obj['integration_id'] ?? '',
            'is_3d_secure'            => ($obj['is_3d_secure'] ?? false) === true ? 'true' : 'false',
            'is_auth'                 => ($obj['is_auth'] ?? false) === true ? 'true' : 'false',
            'is_capture'              => ($obj['is_capture'] ?? false) === true ? 'true' : 'false',
            'is_refunded'             => ($obj['is_refunded'] ?? false) === true ? 'true' : 'false',
            'is_standalone_payment'   => ($obj['is_standalone_payment'] ?? false) === true ? 'true' : 'false',
            'is_voided'               => ($obj['is_voided'] ?? false) === true ? 'true' : 'false',
            'order'                   => isset($obj['order']['id']) ? $obj['order']['id'] : ($obj['order'] ?? ''),
            'owner'                   => $obj['owner'] ?? '',
            'pending'                 => ($obj['pending'] ?? false) === true ? 'true' : 'false',
            'source_data.pan'         => $obj['source_data']['pan'] ?? '',
            'source_data.sub_type'    => $obj['source_data']['sub_type'] ?? '',
            'source_data.type'        => $obj['source_data']['type'] ?? '',
            'success'                 => ($obj['success'] ?? false) === true ? 'true' : 'false',
        ];

        $concatenated = implode('', $hmacFields);
        $calculated   = hash_hmac('sha512', $concatenated, $this->hmacSecret);

        return hash_equals($calculated, $receivedHmac);
    }

    // ─── Webhook Callback Handler ───────────────────────────────────

    /**
     * Process the Paymob webhook callback.
     * Updates payment status, cleans up duplicates, advances app stage, logs & notifies.
     */
    public function handleCallback(array $obj): void
    {
        $merchantOrderId = $obj['order']['merchant_order_id'] ?? '';

        if (!$merchantOrderId) {
            return;
        }

        $payment = Payment::where('transaction_reference', $merchantOrderId)->first();

        if (!$payment || $payment->status === 'completed') {
            return;
        }

        $isSuccess = ($obj['success'] ?? false) === true;
        $newStatus  = $isSuccess ? 'completed' : 'failed';

        DB::transaction(function () use ($payment, $obj, $isSuccess, $newStatus) {
            // Update payment record
            $payment->update([
                'status'                 => $newStatus,
                'gateway_transaction_id' => $obj['id'] ?? null,
                'gateway_response'       => $obj,
                'paid_at'                => $isSuccess ? now() : null,
            ]);

            if (!$isSuccess) {
                return;
            }

            // Cleanup: mark other pending attempts for same app/phase as failed
            Payment::where('application_id', $payment->application_id)
                ->where('phase', $payment->phase)
                ->where('id', '!=', $payment->id)
                ->where('status', 'pending')
                ->update(['status' => 'failed']);

            // Advance application stage to 'approved'
            $application = Application::find($payment->application_id);

            if ($application && $application->current_stage === 'awaiting_payment') {
                $application->current_stage = 'approved';
                $application->save();

                // Log
                Log::create([
                    'application_id' => $application->id,
                    'user_id'        => $application->student_id,
                    'action'         => "Payment completed for [{$application->serial_number}]",
                    'type'           => 'payment',
                ]);

                // Notify student
                Notification::create([
                    'user_id'        => $application->student_id,
                    'application_id' => $application->id,
                    'message'        => "تم تأكيد استلام رسوم طلبك رقم {$application->serial_number} بنجاح. تمت الموافقة على الطلب.",
                    'channel'        => 'system',
                ]);
            }
        });
    }

    // ─── Query Methods ──────────────────────────────────────────────

    /**
     * Get pending payments for a student.
     */
    public function getPendingPayments(int $studentId, array $options = []): array
    {
        $subQuery = Payment::select(DB::raw('MAX(id)'))
            ->whereHas('application', function ($aq) use ($studentId) {
                $aq->where('student_id', $studentId)
                   ->where('current_stage', 'awaiting_payment');
            })
            ->groupBy('application_id');

        $query = Payment::whereIn('id', $subQuery)
            ->with('application:id,title,serial_number,current_stage');

        // Search
        if (!empty($options['search'])) {
            $search = $options['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('application', function ($aq) use ($search) {
                    $aq->where('title', 'like', "%{$search}%")
                       ->orWhere('serial_number', 'like', "%{$search}%");
                })
                ->orWhere('id', $search);
            });
        }

        // Filters
        if (isset($options['min_amount'])) {
            $query->where('amount', '>=', (float) $options['min_amount']);
        }
        if (isset($options['max_amount'])) {
            $query->where('amount', '<=', (float) $options['max_amount']);
        }
        if (!empty($options['start_date'])) {
            $query->whereDate('created_at', '>=', $options['start_date']);
        }
        if (!empty($options['end_date'])) {
            $query->whereDate('created_at', '<=', $options['end_date']);
        }

        // Sorting
        $sortBy = $options['sort_by'] ?? 'created_at';
        $sortOrder = strtolower($options['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        if ($sortBy === 'amount') {
            $query->orderBy('amount', $sortOrder);
        } else {
            $query->orderBy('created_at', $sortOrder);
        }

        // Pagination
        $perPage = (int) ($options['per_page'] ?? 10);
        $paginator = $query->paginate($perPage);

        // Map items to match old schema structure
        $items = collect($paginator->items())->map(function ($payment) {
            return [
                'application_id' => $payment->application->id,
                'serial_number'  => $payment->application->serial_number,
                'title'          => $payment->application->title,
                'current_stage'  => $payment->application->current_stage,
                'amount'         => $payment->amount,
                'payment_id'     => $payment->id,
            ];
        })->toArray();

        return [
            'data' => $items,
            'pagination' => [
                'total'    => $paginator->total(),
                'page'     => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
            ]
        ];
    }

    /**
     * Get payment history for a student.
     */
    public function getPaymentHistory(int $studentId, array $options = []): array
    {
        $query = Payment::whereHas('application', function ($q) use ($studentId) {
            $q->where('student_id', $studentId);
        })
        ->with('application:id,title,serial_number');

        // Search
        if (!empty($options['search'])) {
            $search = $options['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('application', function ($aq) use ($search) {
                    $aq->where('title', 'like', "%{$search}%")
                       ->orWhere('serial_number', 'like', "%{$search}%");
                })
                ->orWhere('transaction_reference', 'like', "%{$search}%")
                ->orWhere('gateway_transaction_id', 'like', "%{$search}%");
            });
        }

        // Filters
        if (!empty($options['status'])) {
            $query->where('status', $options['status']);
        }
        if (isset($options['min_amount'])) {
            $query->where('amount', '>=', (float) $options['min_amount']);
        }
        if (isset($options['max_amount'])) {
            $query->where('amount', '<=', (float) $options['max_amount']);
        }
        if (!empty($options['start_date'])) {
            $query->whereDate('created_at', '>=', $options['start_date']);
        }
        if (!empty($options['end_date'])) {
            $query->whereDate('created_at', '<=', $options['end_date']);
        }

        // Sorting
        $sortBy = $options['sort_by'] ?? 'created_at';
        $sortOrder = strtolower($options['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        if ($sortBy === 'amount') {
            $query->orderBy('amount', $sortOrder);
        } elseif ($sortBy === 'paid_at') {
            $query->orderBy('paid_at', $sortOrder);
        } else {
            $query->orderBy('created_at', $sortOrder);
        }

        // Pagination
        $perPage = (int) ($options['per_page'] ?? 10);
        $paginator = $query->paginate($perPage);

        return [
            'data' => $paginator->items(),
            'pagination' => [
                'total'    => $paginator->total(),
                'page'     => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
            ]
        ];
    }

    /**
     * Get all payments with student info + aggregate stats (admin dashboard).
     */
    public function getAllPaymentsWithStats(array $options = []): array
    {
        $query = Payment::with([
            'application:id,title,serial_number,student_id',
            'application.student:id,full_name,email',
        ]);

        // Search
        if (!empty($options['search'])) {
            $search = $options['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('application', function ($aq) use ($search) {
                    $aq->where('title', 'like', "%{$search}%")
                       ->orWhere('serial_number', 'like', "%{$search}%")
                       ->orWhereHas('student', function ($sq) use ($search) {
                           $sq->where('full_name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                       });
                })
                ->orWhere('transaction_reference', 'like', "%{$search}%")
                ->orWhere('gateway_transaction_id', 'like', "%{$search}%")
                ->orWhere('id', $search);
            });
        }

        // Filters
        if (!empty($options['status'])) {
            $query->where('status', $options['status']);
        }
        if (isset($options['min_amount'])) {
            $query->where('amount', '>=', (float) $options['min_amount']);
        }
        if (isset($options['max_amount'])) {
            $query->where('amount', '<=', (float) $options['max_amount']);
        }
        if (!empty($options['start_date'])) {
            $query->whereDate('created_at', '>=', $options['start_date']);
        }
        if (!empty($options['end_date'])) {
            $query->whereDate('created_at', '<=', $options['end_date']);
        }

        // Sorting
        $sortBy = $options['sort_by'] ?? 'created_at';
        $sortOrder = strtolower($options['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        if ($sortBy === 'amount') {
            $query->orderBy('amount', $sortOrder);
        } elseif ($sortBy === 'paid_at') {
            $query->orderBy('paid_at', $sortOrder);
        } elseif ($sortBy === 'student_name') {
            // Join tables for sorting by student name
            $query->select('payments.*')
                ->join('applications', 'payments.application_id', '=', 'applications.id')
                ->join('users', 'applications.student_id', '=', 'users.id')
                ->orderBy('users.full_name', $sortOrder);
        } else {
            $query->orderBy('payments.created_at', $sortOrder);
        }

        // Calculate Stats over the filtered query BEFORE applying pagination
        $statsQueryCompleted = clone $query;
        $statsQueryPending   = clone $query;
        $statsQueryFailed    = clone $query;

        $totalRevenue   = (float) $statsQueryCompleted->where('status', 'completed')->sum('amount');
        $completedCount = $statsQueryCompleted->where('status', 'completed')->count();
        $pendingCount   = $statsQueryPending->where('status', 'pending')->count();
        $failedCount    = $statsQueryFailed->where('status', 'failed')->count();

        // Pagination
        $perPage = (int) ($options['per_page'] ?? 10);
        $paginator = $query->paginate($perPage);

        return [
            'payments' => $paginator->items(),
            'stats'    => [
                'total_revenue'   => round($totalRevenue, 2),
                'completed_count' => $completedCount,
                'pending_count'   => $pendingCount,
                'failed_count'    => $failedCount,
            ],
            'pagination' => [
                'total'    => $paginator->total(),
                'page'     => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
            ]
        ];
    }

    /**
     * Get receipt data for a single payment.
     * If $studentId is provided, verifies ownership.
     */
    public function getReceipt(int $paymentId, ?int $studentId = null): ?array
    {
        $query = Payment::with('application:id,title,serial_number,student_id')
            ->where('id', $paymentId);

        $payment = $query->first();

        if (!$payment) {
            return null;
        }

        // Verify ownership if studentId provided
        if ($studentId && $payment->application->student_id !== $studentId) {
            return null;
        }

        return [
            'id'                      => $payment->id,
            'application_id'          => $payment->application_id,
            'serial_number'           => $payment->application->serial_number,
            'title'                   => $payment->application->title,
            'phase'                   => $payment->phase,
            'amount'                  => $payment->amount,
            'status'                  => $payment->status,
            'provider'                => $payment->provider,
            'transaction_reference'   => $payment->transaction_reference,
            'gateway_transaction_id'  => $payment->gateway_transaction_id,
            'paid_at'                 => $payment->paid_at?->toDateTimeString(),
            'created_at'              => $payment->created_at?->toDateTimeString(),
        ];
    }
}
