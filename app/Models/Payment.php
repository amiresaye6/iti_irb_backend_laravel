<?php

namespace App\Models;

use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'application_id',
        'phase',
        'amount',
        'provider',
        'transaction_reference',
        'gateway_transaction_id',
        'status',
        'gateway_response',
        'paid_at',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'gateway_response' => 'array',
            'paid_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    // ─── Relationships ───

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
