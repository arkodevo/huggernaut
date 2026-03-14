<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // formula:    structural pattern for learners — e.g. "[Fluid] + 流" or "跟 + [Person] + 交流"
        // usage_note: editorial note on usage, register, syntax constraints, or common collocations

        Schema::table('word_sense_definitions', function (Blueprint $table) {
            $table->string('formula')->nullable()->after('definition_text');
            $table->text('usage_note')->nullable()->after('formula');
        });
    }

    public function down(): void
    {
        Schema::table('word_sense_definitions', function (Blueprint $table) {
            $table->dropColumn(['formula', 'usage_note']);
        });
    }
};
