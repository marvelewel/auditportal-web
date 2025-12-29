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
        Schema::create('findings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Relasi ke AuditRKO (Foreign Key UUID)
            $table->foreignUuid('audit_rko_id')
                ->constrained('audit_rkos')
                ->onDelete('cascade');

            $table->string('jenis_temuan', 20)->index(); // Compliance, Substantive, dll
            $table->text('deskripsi_temuan');
            $table->text('akar_penyebab')->nullable();
            $table->string('kategori', 16)->index(); // MAJOR, MINOR, OBSERVASI
            $table->text('tindakan_perbaikan')->nullable();
            $table->string('pic_auditee', 128)->index();
            $table->date('due_date')->index();
            $table->string('status_finding', 8)->default('OPEN')->index();
            $table->decimal('potential_loss', 18, 2)->default(0);
            
            $table->timestamps();

            // Index tambahan sesuai kebutuhan Django Meta
            $table->index(['status_finding', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('findings');
    }
};