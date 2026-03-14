<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Filter index for POS — derived from word_sense_definitions rows.
        // Maintained by the application: adding/removing a definition row
        // creates/removes the corresponding word_sense_pos row.
        // Kept separate for filter query performance (avoids aggregating definitions).
        Schema::create('word_sense_pos', function (Blueprint $table) {
            $table->foreignId('word_sense_id')
                ->constrained('word_senses')
                ->cascadeOnDelete();
            $table->foreignId('pos_id')
                ->constrained('pos_labels')
                ->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->primary(['word_sense_id', 'pos_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('word_sense_pos');
    }
};
