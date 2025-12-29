<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Finding extends Model
{
    use HasFactory, HasUuids;

    /**
     * ======================================================
     * TABLE
     * ======================================================
     */
    protected $table = 'findings';

    /**
     * ======================================================
     * MASS ASSIGNMENT
     * ======================================================
     */
    protected $fillable = [
        'audit_rko_id',
        'jenis_temuan',
        'kategori',
        'potential_loss',
        'deskripsi_temuan',
        'akar_penyebab',
        'tindakan_perbaikan',
        'pic_auditee',
        'due_date',
        'status_finding',
    ];

    /**
     * ======================================================
     * RELATION: Finding → AuditRKO
     * ======================================================
     */
    public function rko(): BelongsTo
    {
        return $this->belongsTo(AuditRKO::class, 'audit_rko_id');
    }

    /**
     * ======================================================
     * RELATION: Finding → FollowUps
     * ======================================================
     */
    public function followups(): HasMany
    {
        return $this->hasMany(Followup::class, 'finding_id');
    }
}
