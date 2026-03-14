<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per designation per theme.
        // Covers both system themes and user custom themes (same structure).
        // icon_alt doubles as the onboarding tooltip text — the learner learns
        // the icon's meaning here before it appears without a label at higher fluency levels.
        Schema::create('designation_icons', function (Blueprint $table) {
            $table->foreignId('designation_id')
                ->constrained('designations')
                ->cascadeOnDelete();
            $table->foreignId('icon_theme_id')
                ->constrained('icon_themes')
                ->cascadeOnDelete();
            $table->string('icon_value');               // "🦋" · "butterfly.svg" · "/icons/brush/literary.png"
            $table->string('icon_alt')->nullable();     // "literary register — floats like classical poetry"
            $table->primary(['designation_id', 'icon_theme_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('designation_icons');
    }
};
