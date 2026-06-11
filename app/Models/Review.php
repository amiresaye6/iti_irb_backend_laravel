<?php

namespace App\Models;

use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory;

    protected $fillable = [
        'application_id',
        'reviewer_id',
        'assigned_by',
        'assigned_at',
        'assignment_status',
        'decision',
        'refusal_reason',
        'reviewed_at',
        'review_document',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    // ─── Relationships ───

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function comments()
    {
        return $this->hasMany(ReviewComment::class);
    }
}
