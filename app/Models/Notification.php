<?php

namespace App\Models;

use Database\Factories\NotificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    /** @use HasFactory<NotificationFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'application_id',
        'message',
        'channel',
        'is_read',
        'email_sent',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'email_sent' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    // ─── Relationships ───

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
