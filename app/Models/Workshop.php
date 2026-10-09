<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workshop extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'instructor',
        'date_time',
        'capacity',
        'status',
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'capacity' => 'integer',
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function activeRegistrations(): HasMany
    {
        return $this->hasMany(Registration::class)->where('status', 'active');
    }

    // Helper attribute: remaining available seats
    public function getAvailableSeatsAttribute(): int
    {
        return max(0, $this->capacity - $this->activeRegistrations()->count());
    }

    // app/Models/Workshop.php ඇතුළට එක් කරන්න:

public function waitlistedRegistrations(): HasMany
{
    return $this->hasMany(Registration::class)->where('status', 'waitlisted')->orderBy('created_at', 'asc');
}

public function getWaitlistCountAttribute(): int
{
    return $this->waitlistedRegistrations()->count();
}


public function auditLogs()
{
    return $this->morphMany(AuditLog::class, 'auditable')->latest();
}
}