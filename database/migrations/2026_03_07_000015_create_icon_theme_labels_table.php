<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // i18n labels for system themes only.
        // User custom theme names are stored directly on icon_themes.name.
        Schema::create('icon_theme_labels', function (Blueprint $table) {
            $table->foreignId('icon_theme_id')
                ->constrained('icon_themes')
                ->cascadeOnDelete();
            $table->foreignId('language_id')
                ->constrained('languages')
                ->cascadeOnDelete();
            $table->string('label');
            $table->primary(['icon_theme_id', 'language_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icon_theme_labels');
    }
};
