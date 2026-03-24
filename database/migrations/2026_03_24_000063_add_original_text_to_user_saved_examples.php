<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_saved_examples', function (Blueprint $table) {
            $table->text('original_chinese_text')->nullable()->after('english_text');
        });
    }

    public function down(): void
    {
        Schema::table('user_saved_examples', function (Blueprint $table) {
            $table->dropColumn('original_chinese_text');
        });
    }
};
