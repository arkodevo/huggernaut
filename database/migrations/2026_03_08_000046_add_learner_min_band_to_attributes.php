<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// learner_min_band: the TOCFL/HSK band at which this attribute's filter chip
// is shown to learners by default.  0 = always visible.  1–6 = bands.
// This is a RECOMMENDATION, not enforcement — learners can override via
// users.filter_attribute_overrides.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->unsignedTinyInteger('learner_min_band')
                  ->default(0)
                  ->after('tier_required')
                  ->comment('0 = always show; 1–6 = min TOCFL/HSK band before chip appears by default');
        });
    }

    public function down(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->dropColumn('learner_min_band');
        });
    }
};
