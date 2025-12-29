<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('follow_ups', function (Blueprint $table) {

            // ❌ HAPUS KOLON LAMA
            if (Schema::hasColumn('follow_ups', 'user_id')) {
                $table->dropColumn('user_id');
            }

            if (Schema::hasColumn('follow_ups', 'url_bukti_fu')) {
                $table->dropColumn('url_bukti_fu');
            }

            if (Schema::hasColumn('follow_ups', 'evidence_file')) {
                $table->dropColumn('evidence_file');
            }

            // ✅ PASTIKAN KOLOM FINAL ADA
            if (! Schema::hasColumn('follow_ups', 'created_by')) {
                $table->uuid('created_by')->nullable()->index();
            }

            if (! Schema::hasColumn('follow_ups', 'evidence_path')) {
                $table->string('evidence_path')->nullable();
            }

            if (! Schema::hasColumn('follow_ups', 'evidence_name')) {
                $table->string('evidence_name')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
