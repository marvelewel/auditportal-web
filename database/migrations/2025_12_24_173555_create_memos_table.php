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
    Schema::create('memos', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->string('id_unik', 50)->unique();
        $table->string('no_dokumen', 100);
        $table->string('perihal_dokumen', 255);
        $table->string('jenis_dokumen', 20);
        $table->date('tanggal_terbit');
        $table->string('dept_author', 20);
        $table->string('ruang_lingkup', 100); 
        $table->string('status', 10);
        $table->string('file_dokumen')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memos');
    }
};
