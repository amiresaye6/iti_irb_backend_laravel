<?php

namespace App\Models;

use Database\Factories\SampleSizeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SampleSize extends Model
{
    /** @use HasFactory<SampleSizeFactory> */
    use HasFactory;

    protected $fillable = [
        'application_id',
        'sampler_id',
        'calculated_size',
        'sample_amount',
        'notes',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'sample_amount' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    // ─── Relationships ───

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function sampler()
    {
        return $this->belongsTo(User::class, 'sampler_id');
    }
}
