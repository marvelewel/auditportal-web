<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Fix causer_id column to support both integer and UUID.
     * Users table uses integer id, but other models use UUID.
     */
    public function up(): void
    {
        // Drop existing indexes first
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['causer_id']);
            $table->dropIndex(['causer_id', 'created_at']);
        });

        // Change causer_id from uuid to string to be flexible
        DB::statement('ALTER TABLE activity_logs ALTER COLUMN causer_id TYPE VARCHAR(255)');

        // Recreate indexes
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index('causer_id');
            $table->index(['causer_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['causer_id']);
            $table->dropIndex(['causer_id', 'created_at']);
        });

        DB::statement('ALTER TABLE activity_logs ALTER COLUMN causer_id TYPE UUID USING causer_id::uuid');

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index('causer_id');
            $table->index(['causer_id', 'created_at']);
        });
    }
};
