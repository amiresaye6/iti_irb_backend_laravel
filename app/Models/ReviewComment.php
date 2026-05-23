<?php

namespace App\Models;

use Database\Factories\ReviewCommentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewComment extends Model
{
    /** @use HasFactory<ReviewCommentFactory> */
    use HasFactory;

    protected $fillable = [
        'review_id',
        'comment',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    // ─── Relationships ───

    public function review()
    {
        return $this->belongsTo(Review::class);
    }
}
