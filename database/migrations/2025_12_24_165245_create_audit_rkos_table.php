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
        Schema::create('audit_rkos', function (Blueprint $table) {
            // Menggunakan UUID sebagai primary key sesuai Django model
            $table->uuid('id')->primary(); 
            
            $table->text('nama_obyek_pemeriksaan');
            $table->string('pic_audit', 128);
            $table->string('departemen_auditee', 128);
            $table->date('tanggal_mulai');
            
            // Status dengan default 'PLAN'
            $table->string('status_audit', 16)->default('PLAN');
            
            // created_at & updated_at otomatis
            $table->timestamps();

            // Indexing untuk performa filter dashboard
            $table->index('status_audit');
            $table->index('departemen_auditee');
            $table->index('tanggal_mulai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_rkos');
    }
};