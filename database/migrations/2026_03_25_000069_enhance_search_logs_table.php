<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('search_logs', function (Blueprint $table) {
            $table->string('session_id', 40)->nullable()->after('user_id');
            $table->string('user_role', 16)->nullable()->after('session_id');
            $table->string('search_type', 16)->default('word')->after('user_role');
            $table->unsignedSmallInteger('known_count')->default(0)->after('results_count');
            $table->unsignedSmallInteger('unknown_count')->default(0)->after('known_count');

            $table->index('session_id');
        });
    }

    public function down(): void
    {
        Schema::table('search_logs', function (Blueprint $table) {
            $table->dropIndex(['session_id']);
            $table->dropColumn(['session_id', 'user_role', 'search_type', 'known_count', 'unknown_count']);
        });
    }
};
