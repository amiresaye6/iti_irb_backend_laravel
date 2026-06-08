<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'role',
        'full_name',
        'email',
        'password',
        'national_id',
        'phone_number',
        'faculty',
        'department',
        'id_front_url',
        'id_back_url',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
             'role' => 'string',
        ];
    }

    // ─── Relationships ───

    public function applications()
    {
        return $this->hasMany(Application::class, 'student_id');
    }

    public function assignedReviews()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class, 'student_id');
    }

    public function signature()
    {
        return $this->hasOne(Signature::class, 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function logs()
    {
        return $this->hasMany(Log::class, 'user_id');
    }

    //helper methods
    public function isAdmin(): bool
    {
    return $this->role === 'admin';
    }

    public function isSuperAdmin(): bool
    {
    return $this->role === 'super_admin';
    }
    public function isStudent(): bool
    {
    return $this->role === 'student';
    }

    public function isReviewer(): bool
    {
    return $this->role === 'reviewer';
    }
    public function isManager(): bool
    {
    return $this->role === 'manager';
    }

    public function hasRole(string|array $roles): bool
    {
    return in_array($this->role, (array) $roles);
    }
}
