<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('search_not_found', function (Blueprint $table) {
            // Make search_log_id nullable (imports have no search log)
            $table->foreignId('search_log_id')->nullable()->change();

            // Source: where the not-found was encountered
            $table->string('source', 16)->default('search')->after('character'); // search, import
            $table->foreignId('user_id')->nullable()->after('source')->constrained()->nullOnDelete();
            $table->foreignId('collection_id')->nullable()->after('user_id')->constrained()->nullOnDelete();

            $table->index('source');
        });
    }

    public function down(): void
    {
        Schema::table('search_not_found', function (Blueprint $table) {
            $table->dropIndex(['source']);
            $table->dropConstrainedForeignId('collection_id');
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('source');
        });
    }
};
