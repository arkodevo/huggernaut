<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifu_daily_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('message_date');
            $table->string('persona_slug', 20);
            $table->text('message_text');
            $table->jsonb('context_snapshot')->nullable();
            $table->string('feedback', 4)->nullable();
            $table->timestamp('feedback_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'message_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifu_daily_messages');
    }
};
