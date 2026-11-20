<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_OFFICER = 'petugas';
    public const ROLE_CITIZEN = 'warga';

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'region_id', 'is_active',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /* Relasi */

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /** Laporan yang dibuat warga. */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    /** Tugas yang didisposisikan ke petugas lapangan. */
    public function assignedReports(): HasMany
    {
        return $this->hasMany(Report::class, 'assigned_to');
    }

    /* Helper peran */

    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    public function isOfficer(): bool
    {
        return $this->hasRole(self::ROLE_OFFICER);
    }

    public function isCitizen(): bool
    {
        return $this->hasRole(self::ROLE_CITIZEN);
    }

    public function roleLabel(): string
    {
        return match (true) {
            $this->isAdmin() => 'Admin Pusat',
            $this->isOfficer() => 'Petugas Lapangan',
            default => 'Warga',
        };
    }

    /** Halaman beranda setelah login, sesuai peran. */
    public function homeRoute(): string
    {
        return match (true) {
            $this->isAdmin() => 'admin.dashboard',
            $this->isOfficer() => 'officer.tasks.index',
            default => 'home',
        };
    }

    /* Accessor */

    public function getInitialsAttribute(): string
    {
        return Str::of($this->name)->explode(' ')->filter()->take(2)
            ->map(fn ($w) => Str::upper(Str::substr($w, 0, 1)))->implode('');
    }

    /** Nama yang ditampilkan ke publik: "Budi S." demi privasi pelapor. */
    public function getPublicNameAttribute(): string
    {
        $parts = array_values(array_filter(explode(' ', trim($this->name))));
        if (count($parts) < 2) {
            return $parts[0] ?? 'Warga';
        }

        return $parts[0] . ' ' . Str::upper(Str::substr($parts[1], 0, 1)) . '.';
    }
}
