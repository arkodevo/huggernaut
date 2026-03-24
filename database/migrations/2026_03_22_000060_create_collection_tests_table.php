<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('collection_id')->constrained()->cascadeOnDelete();
            $table->string('test_mode', 30);            // domain, pos, pinyin, definition, attribute, usage
            $table->string('attribute_slug', 30)->nullable(); // only when test_mode='attribute'
            $table->smallInteger('total_questions');
            $table->smallInteger('clean_count')->default(0);
            $table->smallInteger('assisted_count')->default(0);
            $table->smallInteger('learning_count')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'collection_id']);
            $table->index(['user_id', 'test_mode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_tests');
    }
};
