<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Fix subject_id column to support both integer and UUID.
     * Users table uses integer id, but other models use UUID.
     */
    public function up(): void
    {
        // Drop existing indexes first
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['subject_type', 'subject_id']);
        });

        // Change subject_id from uuid to string to be flexible
        DB::statement('ALTER TABLE activity_logs ALTER COLUMN subject_id TYPE VARCHAR(255)');

        // Recreate indexes
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['subject_type', 'subject_id']);
        });

        DB::statement('ALTER TABLE activity_logs ALTER COLUMN subject_id TYPE UUID USING subject_id::uuid');

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index(['subject_type', 'subject_id']);
        });
    }
};
