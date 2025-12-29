<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuditRKO extends Model
{
    use HasFactory, HasUuids;

    /**
     * ======================================================
     * TABLE & PRIMARY KEY
     * ======================================================
     */
    protected $table = 'audit_rkos';

    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * ======================================================
     * MASS ASSIGNMENT
     * ======================================================
     */
    protected $fillable = [
        'nama_obyek_pemeriksaan',
        'pic_audit',
        'departemen_auditee',
        'tanggal_mulai',
        'status_audit',
    ];

    /**
     * ======================================================
     * PENTING (ANTI BUG FILAMENT / PGSQL)
     * - JANGAN eager load apa pun di sini
     * - JANGAN withCount
     * ======================================================
     */
    protected $with = [];
    protected $withCount = [];

    /**
     * ======================================================
     * RELATION: AuditRKO → Findings
     * SATU-SATUNYA relasi yang SAH
     * ======================================================
     */
    public function findings(): HasMany
    {
        return $this->hasMany(Finding::class, 'audit_rko_id');
    }

    /**
     * ======================================================
     * ❌ DILARANG KERAS:
     * - followups()
     * - hasManyThrough ke Followup
     * ======================================================
     *
     * Follow-Up adalah MILIK Finding,
     * BUKAN milik AuditRKO langsung.
     */
}
