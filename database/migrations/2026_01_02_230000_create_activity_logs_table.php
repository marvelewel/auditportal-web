<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * ============================================================
     * ACTIVITY LOGS TABLE - ENTERPRISE AUDIT TRAIL
     * ============================================================
     * 
     * Implements the 3W Principle:
     * - WHO   → causer_id, causer_type, causer_name
     * - WHAT  → event, description, properties (before/after)
     * - WHEN  → created_at timestamp
     * 
     * Design Decisions:
     * 1. Use TEXT for properties to store large JSON (field changes)
     * 2. Store causer_name for historical reference (user may be deleted)
     * 3. Use index on frequently filtered columns
     * 4. No soft deletes - audit logs are IMMUTABLE
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            // ======================================================
            // PRIMARY KEY - UUID for distributed systems
            // ======================================================
            $table->uuid('id')->primary();

            // ======================================================
            // WHO - User who performed the action
            // ======================================================
            $table->uuid('causer_id')->nullable();           // FK to users (nullable for system actions)
            $table->string('causer_type')->nullable();       // Polymorphic type (App\Models\User)
            $table->string('causer_name')->nullable();       // Cached name (for historical reference)
            $table->string('causer_role')->nullable();       // ADMIN, AUDITOR, AUDITEE
            $table->string('causer_ip', 45)->nullable();     // IP Address (IPv6 support)
            $table->string('causer_agent')->nullable();      // User Agent (browser info)

            // ======================================================
            // WHAT - Action performed
            // ======================================================
            $table->string('log_name')->default('system');   // Log category: 'audit', 'auth', 'system'
            $table->string('event');                          // Action: CREATE, UPDATE, DELETE, LOGIN, etc.
            $table->string('description');                    // Human-readable description

            // Subject - The entity being acted upon
            $table->uuid('subject_id')->nullable();           // FK to the affected record
            $table->string('subject_type')->nullable();       // Polymorphic type (App\Models\Finding)
            $table->string('subject_identifier')->nullable(); // Human-readable identifier (e.g., Finding title)

            // Changes - Field-level tracking (before → after)
            $table->text('properties')->nullable();           // JSON: {old: {}, attributes: {}}

            // ======================================================
            // WHEN - Timestamp
            // ======================================================
            $table->timestamp('created_at')->useCurrent();

            // ======================================================
            // INDEXES for Performance
            // ======================================================
            $table->index('causer_id');
            $table->index('event');
            $table->index('log_name');
            $table->index('subject_type');
            $table->index('created_at');

            // Composite index for common queries
            $table->index(['subject_type', 'subject_id']);
            $table->index(['causer_id', 'created_at']);
            $table->index(['event', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
