<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_saved_examples', function (Blueprint $table) {
            $table->text('ai_feedback')->nullable()->after('ai_verified');
        });
    }

    public function down(): void
    {
        Schema::table('user_saved_examples', function (Blueprint $table) {
            $table->dropColumn('ai_feedback');
        });
    }
};
