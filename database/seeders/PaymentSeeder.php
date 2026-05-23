<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $payments = [
            ['application_id' => 1, 'phase' => 'initial', 'amount' => 500.00, 'provider' => 'Fawry', 'transaction_reference' => 'FW1001', 'gateway_transaction_id' => '511625001', 'status' => 'completed', 'gateway_response' => json_encode(['message' => 'Approved']), 'paid_at' => '2026-03-02 10:00:00', 'created_at' => '2026-03-02 09:50:00'],
            ['application_id' => 1, 'phase' => 'sample', 'amount' => 350.00, 'provider' => 'Fawry', 'transaction_reference' => 'FW1002', 'gateway_transaction_id' => '511625002', 'status' => 'completed', 'gateway_response' => json_encode(['message' => 'Approved']), 'paid_at' => '2026-03-05 12:00:00', 'created_at' => '2026-03-05 11:45:00'],
            ['application_id' => 2, 'phase' => 'initial', 'amount' => 500.00, 'provider' => 'Paymob', 'transaction_reference' => 'PM2001', 'gateway_transaction_id' => '511625436', 'status' => 'completed', 'gateway_response' => json_encode(['message' => 'Approved', 'source_data' => ['type' => 'card', 'sub_type' => 'MasterCard']]), 'paid_at' => '2026-04-11 09:00:00', 'created_at' => '2026-04-11 08:55:00'],
            ['application_id' => 2, 'phase' => 'sample', 'amount' => 800.00, 'provider' => 'Fawry', 'transaction_reference' => 'FW2002', 'gateway_transaction_id' => '511625004', 'status' => 'completed', 'gateway_response' => json_encode(['message' => 'Approved']), 'paid_at' => '2026-04-14 10:00:00', 'created_at' => '2026-04-14 09:50:00'],
            ['application_id' => 3, 'phase' => 'initial', 'amount' => 500.00, 'provider' => 'InstaPay', 'transaction_reference' => 'IP3001', 'gateway_transaction_id' => '511625005', 'status' => 'completed', 'gateway_response' => json_encode(['message' => 'Approved']), 'paid_at' => '2026-04-16 11:00:00', 'created_at' => '2026-04-16 10:55:00'],
            ['application_id' => 4, 'phase' => 'initial', 'amount' => 500.00, 'provider' => 'Fawry', 'transaction_reference' => 'FW4001', 'gateway_transaction_id' => '511625006', 'status' => 'completed', 'gateway_response' => json_encode(['message' => 'Approved']), 'paid_at' => '2026-02-21 10:00:00', 'created_at' => '2026-02-21 09:55:00'],
            ['application_id' => 4, 'phase' => 'sample', 'amount' => 400.00, 'provider' => 'Fawry', 'transaction_reference' => 'FW4002', 'gateway_transaction_id' => '511625007', 'status' => 'completed', 'gateway_response' => json_encode(['message' => 'Approved']), 'paid_at' => '2026-02-25 10:00:00', 'created_at' => '2026-02-25 09:50:00'],
            ['application_id' => 5, 'phase' => 'initial', 'amount' => 500.00, 'provider' => 'Paymob', 'transaction_reference' => 'PM5001', 'gateway_transaction_id' => '511676577', 'status' => 'pending', 'gateway_response' => null, 'paid_at' => null, 'created_at' => '2026-04-23 19:43:43'],
        ];

        foreach ($payments as $payment) {
            Payment::create($payment);
        }
    }
}
