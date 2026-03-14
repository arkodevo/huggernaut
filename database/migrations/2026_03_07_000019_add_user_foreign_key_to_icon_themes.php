<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Resolves the circular dependency between icon_themes and users.
        // icon_themes was created before users was extended, so user_id was
        // added as a plain column. Now that users exists in full, the FK is safe to add.
        Schema::table('icon_themes', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('icon_themes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
    }
};
