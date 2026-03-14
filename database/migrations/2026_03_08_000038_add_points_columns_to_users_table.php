<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Spendable balance — decrements when points are converted to AI credits.
            $table->unsignedInteger('points_balance')->default(0)->after('ai_credits_reset_at');
            // Lifetime total — monotonically increasing. Drives level and badge eligibility.
            $table->unsignedInteger('points_total_earned')->default(0)->after('points_balance');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['points_balance', 'points_total_earned']);
        });
    }
};
