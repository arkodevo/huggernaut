<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('default_writings_public')->default(true)->after('fluency_level');
            $table->boolean('default_disputes_anonymous')->default(false)->after('default_writings_public');
            $table->timestamp('last_seen_activity_at')->nullable()->after('default_disputes_anonymous');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'default_writings_public',
                'default_disputes_anonymous',
                'last_seen_activity_at',
            ]);
        });
    }
};
