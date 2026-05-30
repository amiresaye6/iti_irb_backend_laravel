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

        if ($application->current_stage !== 'approved_by_reviewer') {
            return response()->json([
                'status'  => false,
                'message' => 'Application is not at the approved_by_reviewer stage.',
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

        // Find the pending payment record
        $payment = Payment::where('application_id', $applicationId)
            ->where('status', 'pending')
            ->latest('created_at')
            ->first();

        if (!$payment) {
            return response()->json([
                'status'  => false,
                'message' => 'No pending payment found for this application.',
            ], 404);
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
    public function pendingPayments(): JsonResponse
    {
        $studentId = auth()->id();
        $pending = $this->paymentService->getPendingPayments($studentId);

        return response()->json([
            'status'  => true,
            'message' => 'Pending payments retrieved.',
            'data'    => $pending,
        ], 200);
    }

    // ─── Student: Payment History ───────────────────────────────────

    /**
     * Get the logged-in student's full payment history.
     */
    public function history(): JsonResponse
    {
        $studentId = auth()->id();
        $history = $this->paymentService->getPaymentHistory($studentId);

        return response()->json([
            'status'  => true,
            'message' => 'Payment history retrieved.',
            'data'    => $history,
        ], 200);
    }

    // ─── Admin: All Payments + Stats ────────────────────────────────

    /**
     * Admin/manager dashboard: all payments with student info and aggregate stats.
     */
    public function adminIndex(): JsonResponse
    {
        $result = $this->paymentService->getAllPaymentsWithStats();

        return response()->json([
            'status'  => true,
            'message' => 'All payments retrieved.',
            'data'    => $result,
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

    /**
     * Check payment status via Paymob API (frontend polling after redirect).
     */
    public function verify($clientSecret): JsonResponse
    {
        $result = $this->paymentService->getIntentionDetails($clientSecret);

        if ($result['success']) {
            return response()->json([
                'status'  => true,
                'message' => 'Payment status retrieved.',
                'data'    => $result,
            ], 200);
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
