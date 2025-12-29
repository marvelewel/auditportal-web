<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('follow_ups', function (Blueprint $table) {
            if (Schema::hasColumn('follow_ups', 'role_pengisi')) {
                $table->dropColumn('role_pengisi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('follow_ups', function (Blueprint $table) {
            if (! Schema::hasColumn('follow_ups', 'role_pengisi')) {
                $table->string('role_pengisi', 20)->nullable();
            }
        });
    }
};
