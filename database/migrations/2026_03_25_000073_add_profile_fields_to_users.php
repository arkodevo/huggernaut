<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pll_name', 255)->nullable()->after('name');
            $table->string('chinese_name', 32)->nullable()->after('pll_name');
            $table->string('chinese_name_pinyin', 64)->nullable()->after('chinese_name');
            $table->text('chinese_name_meaning')->nullable()->after('chinese_name_pinyin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['pll_name', 'chinese_name', 'chinese_name_pinyin', 'chinese_name_meaning']);
        });
    }
};
