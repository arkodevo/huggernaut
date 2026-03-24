<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add fluency level to users (profile setting for 師父)
        Schema::table('users', function (Blueprint $table) {
            $table->string('fluency_level', 20)->nullable()->after('level_framework');
        });

        // Add assessment columns to saved examples
        Schema::table('user_saved_examples', function (Blueprint $table) {
            $table->string('assessed_level', 20)->nullable()->after('source_type');
            $table->string('assessed_mastery', 20)->nullable()->after('assessed_level');
            $table->text('mastery_guidance')->nullable()->after('assessed_mastery');

            // Indexes for community search
            $table->index('assessed_level');
            $table->index('assessed_mastery');
            $table->index(['assessed_level', 'assessed_mastery']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('fluency_level');
        });

        Schema::table('user_saved_examples', function (Blueprint $table) {
            $table->dropIndex(['assessed_level', 'assessed_mastery']);
            $table->dropIndex(['assessed_mastery']);
            $table->dropIndex(['assessed_level']);
            $table->dropColumn(['assessed_level', 'assessed_mastery', 'mastery_guidance']);
        });
    }
};
