<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Role — admin and editor roles gate access to the admin panel.
            $table->string('role', 16)->default('user')->after('id');
            // user · editor · admin

            // UI & display preferences
            $table->foreignId('ui_language_id')
                ->nullable()
                ->after('role')
                ->constrained('languages')
                ->nullOnDelete();
            $table->string('script_preference', 16)->default('traditional')->after('ui_language_id');
            // traditional · simplified · both
            $table->string('pos_mode', 16)->default('standard')->after('script_preference');
            // simplified · standard · full

            // Level framework preference — controls which Level attribute appears in the filter bar.
            // tocfl: moon-phase icons, Taiwan curriculum
            // hsk:   growth icons, mainland curriculum
            // The filter chip machinery is identical for both; only the designation set swaps.
            $table->string('level_framework', 8)->default('tocfl')->after('pos_mode');
            // tocfl · hsk

            // Subscription & credits
            // subscription_tier is a cached/derived value alongside Cashier's source of truth.
            $table->string('subscription_tier', 16)->default('free')->after('level_framework');
            // free · entry · mid · pro
            $table->unsignedInteger('ai_credits_remaining')->default(0)->after('subscription_tier');
            $table->timestamp('ai_credits_reset_at')->nullable()->after('ai_credits_remaining');

            // Icon theme — points to a system theme or user's own forked custom theme.
            // null falls back to the system default (icon_themes.is_default = true).
            $table->foreignId('icon_theme_id')
                ->nullable()
                ->after('ai_credits_reset_at')
                ->constrained('icon_themes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['ui_language_id']);
            $table->dropForeign(['icon_theme_id']);
            $table->dropColumn([
                'role',
                'ui_language_id',
                'script_preference',
                'pos_mode',
                'level_framework',
                'subscription_tier',
                'ai_credits_remaining',
                'ai_credits_reset_at',
                'icon_theme_id',
            ]);
        });
    }
};
