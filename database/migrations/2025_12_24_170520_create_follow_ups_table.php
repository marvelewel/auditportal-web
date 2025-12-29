<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follow_ups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Relasi ke Finding
            $table->foreignUuid('finding_id')
                ->constrained('findings')
                ->onDelete('cascade');

            // Siapa yang mencatat (relasi ke tabel users bawaan Laravel)
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->dateTime('tanggal_fu');
            $table->text('keterangan');
            $table->string('url_bukti_fu')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_ups');
    }
};