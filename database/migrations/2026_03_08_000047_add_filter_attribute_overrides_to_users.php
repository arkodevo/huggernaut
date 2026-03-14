<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Stores learner's explicit visibility overrides for filter attribute chips.
// Format: { "attribute_slug": true|false }
// true = user has forced-on an attribute that would otherwise be hidden by learner_min_band.
// false = user has hidden an attribute that would otherwise be visible.
// Null (column absent / null) = use learner_min_band defaults.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->jsonb('filter_attribute_overrides')
                  ->nullable()
                  ->after('level_framework');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('filter_attribute_overrides');
        });
    }
};
