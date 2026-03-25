<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shifu_engagements', function (Blueprint $table) {
            $table->unsignedTinyInteger('audit_grade')->nullable()->after('completed_at');
            $table->text('audit_feedback')->nullable()->after('audit_grade');
            $table->timestamp('audit_reviewed_at')->nullable()->after('audit_feedback');
        });
    }

    public function down(): void
    {
        Schema::table('shifu_engagements', function (Blueprint $table) {
            $table->dropColumn(['audit_grade', 'audit_feedback', 'audit_reviewed_at']);
        });
    }
};
