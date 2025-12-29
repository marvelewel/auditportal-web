<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * ======================================================
     * MASS ASSIGNMENT
     * ======================================================
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * ======================================================
     * ROLE CONSTANTS (ANTI TYPO)
     * ======================================================
     */
    public const ROLE_ADMIN   = 'ADMIN';
    public const ROLE_AUDITOR = 'AUDITOR';
    public const ROLE_AUDITEE = 'AUDITEE';

    /**
     * ======================================================
     * ROLE HELPERS
     * ======================================================
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isAuditor(): bool
    {
        return $this->role === self::ROLE_AUDITOR;
    }

    public function isAuditee(): bool
    {
        return $this->role === self::ROLE_AUDITEE;
    }

    /**
     * ======================================================
     * GENERIC ROLE CHECK (OPTIONAL, TAPI BERGUNA)
     * ======================================================
     */
    public function hasRole(string $role): bool
    {
        return $this->role === strtoupper($role);
    }
}
