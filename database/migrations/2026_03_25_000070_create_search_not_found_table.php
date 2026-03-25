<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_not_found', function (Blueprint $table) {
            $table->id();
            $table->foreignId('search_log_id')->constrained('search_logs')->cascadeOnDelete();
            $table->string('character', 16);
            $table->timestamp('created_at')->useCurrent();

            $table->index('character');
            $table->index('search_log_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_not_found');
    }
};
