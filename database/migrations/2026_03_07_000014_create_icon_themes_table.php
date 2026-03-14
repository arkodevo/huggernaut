<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icon_themes', function (Blueprint $table) {
            $table->id();

            // Nullable — set after users table exists (FK added in migration 000019).
            // null = system theme shared by all users.
            // non-null = user-owned custom theme (forked from a system theme).
            $table->unsignedBigInteger('user_id')->nullable();

            // Which system theme this was forked from (null for system themes themselves).
            $table->foreignId('source_theme_id')
                ->nullable()
                ->constrained('icon_themes')
                ->nullOnDelete();

            $table->string('slug', 64)->unique();
            // System themes: English name seeded; display resolved via icon_theme_labels.
            // User custom themes: personal name stored here directly.
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon_type', 16)->default('emoji'); // emoji · svg · image
            $table->boolean('is_active')->default(true);       // controls picker visibility for system themes
            $table->boolean('is_default')->default(false);     // one system theme only
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icon_themes');
    }
};
