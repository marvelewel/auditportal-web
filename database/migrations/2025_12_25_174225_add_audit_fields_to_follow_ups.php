<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('follow_ups', function (Blueprint $table) {

            /**
             * ======================================================
             * AUDIT TRAIL
             * Siapa yang menambahkan follow-up
             * ======================================================
             */
            $table->unsignedBigInteger('created_by')
                ->after('finding_id');

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            /**
             * ======================================================
             * BUKTI FOLLOW-UP
             * ======================================================
             */
            $table->string('evidence_file')
                ->nullable()
                ->after('keterangan');
        });
    }

    public function down(): void
    {
        Schema::table('follow_ups', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'created_by',
                'evidence_file',
            ]);
        });
    }
};
