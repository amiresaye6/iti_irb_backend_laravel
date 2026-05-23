<?php

namespace App\Models;

use Database\Factories\ApplicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    /** @use HasFactory<ApplicationFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'serial_number',
        'title',
        'principal_investigator',
        'current_stage',
        'is_blinded',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'is_blinded' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ─── Relationships ───

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function coInvestigators()
    {
        return $this->hasMany(CoInvestigator::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function sampleSizes()
    {
        return $this->hasMany(SampleSize::class);
    }

    public function keywords()
    {
        return $this->hasMany(Keyword::class);
    }

    public function certificate()
    {
        return $this->hasOne(Certificate::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function logs()
    {
        return $this->hasMany(Log::class);
    }
}
