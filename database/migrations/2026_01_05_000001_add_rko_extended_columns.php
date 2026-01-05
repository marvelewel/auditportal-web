<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('audit_rkos', function (Blueprint $table) {
            // ======================================================
            // IDENTITY FIELDS
            // ======================================================
            $table->string('no_urut_laporan', 50)->nullable()->after('status_audit');
            $table->string('pic_tl_pemeriksaan', 128)->nullable()->after('no_urut_laporan');
            $table->string('inisial_pic', 10)->nullable()->after('pic_tl_pemeriksaan');
            $table->date('tanggal_komitmen_followup')->nullable()->after('inisial_pic');
            $table->string('sub_departemen', 50)->nullable()->after('departemen_auditee');

            // ======================================================
            // TIMELINE FIELDS (A00 - E00)
            // ======================================================
            $table->date('a00_surat_tugas')->nullable()->after('tanggal_komitmen_followup');
            $table->date('b00_meeting_dts')->nullable()->after('a00_surat_tugas');
            $table->date('c00_notulen')->nullable()->after('b00_meeting_dts');
            $table->date('d00_report_dirut')->nullable()->after('c00_notulen');
            $table->date('e00_report_distribusi')->nullable()->after('d00_report_dirut');

            // ======================================================
            // KPI INTERNAL PROCESS (P-2.1 Temuan)
            // ======================================================
            $table->integer('temuan_major')->default(0)->after('e00_report_distribusi');
            $table->integer('temuan_minor')->default(0)->after('temuan_major');
            $table->integer('temuan_observasi')->default(0)->after('temuan_minor');

            // ======================================================
            // KPI FINANCIAL F-1 (Optimasi Anggaran)
            // ======================================================
            $table->decimal('f1_personnel', 15, 2)->default(0)->after('temuan_observasi');
            $table->decimal('f1_asset', 15, 2)->default(0)->after('f1_personnel');
            $table->decimal('f1_other', 15, 2)->default(0)->after('f1_asset');

            // ======================================================
            // KPI FINANCIAL F-2 (Efisiensi)
            // ======================================================
            $table->decimal('f2_barang', 15, 2)->default(0)->after('f1_other');
            $table->decimal('f2_uang', 15, 2)->default(0)->after('f2_barang');
            $table->decimal('f2_nota', 15, 2)->default(0)->after('f2_uang');
            $table->decimal('f2_lain', 15, 2)->default(0)->after('f2_nota');

            // ======================================================
            // KPI CUSTOMER
            // ======================================================
            $table->integer('c11_skor_survei')->default(0)->after('f2_lain');
            $table->integer('c12_prosedur_dilanggar')->default(0)->after('c11_skor_survei');
            $table->integer('audit_lead_time')->default(0)->after('c12_prosedur_dilanggar');

            // ======================================================
            // CLOUD LINK
            // ======================================================
            $table->text('link_detail_rko')->nullable()->after('audit_lead_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_rkos', function (Blueprint $table) {
            $table->dropColumn([
                // Identity
                'no_urut_laporan',
                'pic_tl_pemeriksaan',
                'inisial_pic',
                'tanggal_komitmen_followup',
                'sub_departemen',
                // Timeline
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
            ]);
        });
    }
};
