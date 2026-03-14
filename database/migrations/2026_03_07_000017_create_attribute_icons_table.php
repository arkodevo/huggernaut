<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Group/header icon for each attribute per theme.
        // Shown in the filter panel as the family header (e.g. 🌊 for Dimension).
        // Not overridable per user — group icons travel with the theme.
        Schema::create('attribute_icons', function (Blueprint $table) {
            $table->foreignId('attribute_id')
                ->constrained('attributes')
                ->cascadeOnDelete();
            $table->foreignId('icon_theme_id')
                ->constrained('icon_themes')
                ->cascadeOnDelete();
            $table->string('icon_value');
            $table->string('icon_alt')->nullable();
            $table->primary(['attribute_id', 'icon_theme_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_icons');
    }
};
