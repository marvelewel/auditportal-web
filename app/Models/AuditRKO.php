<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuditRKO extends Model
{
    use HasFactory, HasUuids, HasActivityLog;

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
        // Original fields
        'nama_obyek_pemeriksaan',
        'pic_audit',
        'departemen_auditee',
        'sub_departemen',
        'tanggal_mulai',
        'status_audit',
        // Identity fields
        'no_urut_laporan',
        'pic_tl_pemeriksaan',
        'inisial_pic',
        'tanggal_komitmen_followup',
        // Timeline fields
        'a00_surat_tugas',
        'b00_meeting_dts',
        'c00_notulen',
        'd00_report_dirut',
        'e00_report_distribusi',
        // KPI Internal Process
        'temuan_major',
        'temuan_minor',
        'temuan_observasi',
        // KPI Financial F-1
        'f1_personnel',
        'f1_asset',
        'f1_other',
        // KPI Financial F-2
        'f2_barang',
        'f2_uang',
        'f2_nota',
        'f2_lain',
        // KPI Customer
        'c11_skor_survei',
        'c12_prosedur_dilanggar',
        'audit_lead_time',
        // Cloud Link
        'link_detail_rko',
    ];

    /**
     * ======================================================
     * TYPE CASTING
     * ======================================================
     */
    protected $casts = [
        // Date fields
        'tanggal_mulai' => 'date',
        'tanggal_komitmen_followup' => 'date',
        'a00_surat_tugas' => 'date',
        'b00_meeting_dts' => 'date',
        'c00_notulen' => 'date',
        'd00_report_dirut' => 'date',
        'e00_report_distribusi' => 'date',
        // Decimal fields
        'f1_personnel' => 'decimal:2',
        'f1_asset' => 'decimal:2',
        'f1_other' => 'decimal:2',
        'f2_barang' => 'decimal:2',
        'f2_uang' => 'decimal:2',
        'f2_nota' => 'decimal:2',
        'f2_lain' => 'decimal:2',
        // Integer fields
        'temuan_major' => 'integer',
        'temuan_minor' => 'integer',
        'temuan_observasi' => 'integer',
        'c11_skor_survei' => 'integer',
        'c12_prosedur_dilanggar' => 'integer',
        'audit_lead_time' => 'integer',
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
