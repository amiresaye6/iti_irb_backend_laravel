<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Services\PaymentService;
use App\Models\Application;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    // ─── Admin: Set Fee ─────────────────────────────────────────────

    /**
     * Admin/manager sets the payment fee for an application.
     * amount = 0 → skip to approved. amount > 0 → awaiting_payment.
     */
    public function setFee(Request $request, $applicationId): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0|max:99999.99',
        ]);

        $application = Application::findOrFail($applicationId);

        if ($application->current_stage !== 'final_review') {
            return response()->json([
                'status'  => false,
                'message' => 'Application is not at the final_review stage.',
            ], 422);
        }

        $result = $this->paymentService->setApplicationFee($application, (float) $validated['amount']);

        if ($result['skipped']) {
            return response()->json([
                'status'  => true,
                'message' => 'Application approved with zero fees.',
                'data'    => ['application' => $result['application']],
            ], 200);
        }

        return response()->json([
            'status'  => true,
            'message' => "Payment of {$validated['amount']} EGP set. Application moved to awaiting payment.",
            'data'    => [
                'application' => $result['application'],
                'payment'     => $result['payment'],
            ],
        ], 201);
    }

    // ─── Student: Checkout ──────────────────────────────────────────

    /**
     * Student initiates Paymob checkout for a pending payment.
     */
    public function checkout($applicationId): JsonResponse
    {
        $user = auth()->user();
        $application = Application::findOrFail($applicationId);

        // Verify ownership
        if ($application->student_id !== $user->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Access denied.',
            ], 403);
        }

        // Verify stage
        if ($application->current_stage !== 'awaiting_payment') {
            return response()->json([
                'status'  => false,
                'message' => 'This application is not pending any payments.',
            ], 422);
        }

        // Find the latest payment record for this application
        $latestPayment = Payment::where('application_id', $applicationId)
            ->latest('id')
            ->first();

        if (!$latestPayment) {
            return response()->json([
                'status'  => false,
                'message' => 'No payment record found for this application.',
            ], 404);
        }

        // Verify stage
        if ($latestPayment->status === 'completed') {
            return response()->json([
                'status'  => false,
                'message' => 'This application has already been paid.',
            ], 422);
        }

        $payment = $latestPayment;

        // If the latest payment already has a transaction reference or is marked 'failed',
        // we create a new payment attempt record to keep history.
        if ($latestPayment->transaction_reference !== null || $latestPayment->status === 'failed') {
            // Mark the old one as failed if it was still pending (e.g. abandoned attempt)
            if ($latestPayment->status === 'pending') {
                $latestPayment->update(['status' => 'failed']);
            }

            // Create a brand new payment record for the new attempt
            $payment = Payment::create([
                'application_id' => $applicationId,
                'phase'          => $latestPayment->phase,
                'amount'         => $latestPayment->amount,
                'provider'       => 'Paymob',
                'status'         => 'pending',
            ]);
        }

        $result = $this->paymentService->createPaymentIntention($application, $user, $payment);

        if ($result['success']) {
            return response()->json([
                'status'  => true,
                'message' => 'Payment intention created successfully.',
                'data'    => [
                    'checkout_url'      => $result['checkout_url'],
                    'client_secret'     => $result['client_secret'],
                    'special_reference' => $result['special_reference'],
                ],
            ], 200);
        }

        return response()->json([
            'status'  => false,
            'message' => 'Failed to create payment intention.',
            'error'   => $result['error'],
        ], 500);
    }

    // ─── Student: Pending Payments ──────────────────────────────────

    /**
     * Get the logged-in student's applications awaiting payment.
     */
    public function pendingPayments(Request $request): JsonResponse
    {
        $studentId = auth()->id();
        $result = $this->paymentService->getPendingPayments($studentId, $request->all());

        return response()->json([
            'status'     => true,
            'message'    => 'Pending payments retrieved.',
            'data'       => $result['data'],
            'pagination' => $result['pagination'],
        ], 200);
    }

    // ─── Student: Payment History ───────────────────────────────────

    /**
     * Get the logged-in student's full payment history.
     */
    public function history(Request $request): JsonResponse
    {
        $studentId = auth()->id();
        $result = $this->paymentService->getPaymentHistory($studentId, $request->all());

        return response()->json([
            'status'     => true,
            'message'    => 'Payment history retrieved.',
            'data'       => $result['data'],
            'pagination' => $result['pagination'],
        ], 200);
    }

    // ─── Admin: All Payments + Stats ────────────────────────────────

    /**
     * Admin/manager dashboard: all payments with student info and aggregate stats.
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $result = $this->paymentService->getAllPaymentsWithStats($request->all());

        return response()->json([
            'status'     => true,
            'message'    => 'All payments retrieved.',
            'data'       => [
                'payments' => $result['payments'],
                'stats'    => $result['stats'],
            ],
            'pagination' => $result['pagination'],
        ], 200);
    }

    // ─── Paymob Webhook Callback ────────────────────────────────────

    /**
     * Paymob webhook callback — no auth, verified via HMAC.
     */
    public function callback(Request $request): JsonResponse
    {
        $data = $request->all();

        if (!isset($data['obj'])) {
            return response()->json(['message' => 'Invalid payload.'], 400);
        }

        $obj = $data['obj'];
        $receivedHmac = $request->query('hmac', '');

        if (!$this->paymentService->verifyHmac($obj, $receivedHmac)) {
            return response()->json(['message' => 'HMAC validation failed.'], 403);
        }

        $this->paymentService->handleCallback($obj);

        return response()->json(['message' => 'Callback received.'], 200);
    }

    // ─── Student: Verify Payment Status ─────────────────────────────

    public function verify($clientSecret): JsonResponse
    {
        $result = $this->paymentService->getIntentionDetails($clientSecret);

        if ($result['success']) {
            $isPaid = $result['is_paid'];
            $specialReference = $result['data']['special_reference'] ?? null;
            
            $payment = null;
            if ($specialReference) {
                $payment = \App\Models\Payment::where('transaction_reference', $specialReference)->first();
            }

            if ($isPaid) {
                if ($payment && $payment->status !== 'completed') {
                    $this->paymentService->completePayment($payment, $result['data']);
                }

                if ($payment) {
                    $payment->refresh();
                }

                return response()->json([
                    'status' => true,
                    'data'   => [
                        'success'    => true,
                        'is_paid'    => true,
                        'payment_id' => $payment ? $payment->id : null,
                    ],
                ], 200);
            } else {
                // If it is not paid, update status to failed locally for logging, but only if it was pending
                if ($payment && $payment->status === 'pending') {
                    $this->paymentService->failPayment($payment, $result['data']);
                }

                return response()->json([
                    'status'  => true,
                    'data'    => [
                        'success' => false,
                        'is_paid' => false,
                    ],
                    'message' => 'فشلت عملية الدفع',
                ], 200);
            }
        }

        return response()->json([
            'status'  => false,
            'message' => 'Failed to retrieve payment status.',
            'error'   => $result['error'],
        ], 500);
    }

    // ─── Receipt ────────────────────────────────────────────────────

    /**
     * Get receipt data for a completed payment.
     * Students can only see their own. Admins/managers can see any.
     */
    public function receipt($paymentId): JsonResponse
    {
        $user = auth()->user();

        // Students can only access their own receipts
        $studentId = $user->isStudent() ? $user->id : null;

        $receipt = $this->paymentService->getReceipt((int) $paymentId, $studentId);

        if (!$receipt) {
            return response()->json([
                'status'  => false,
                'message' => 'Receipt not found or access denied.',
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Receipt retrieved.',
            'data'    => $receipt,
        ], 200);
    }
}
