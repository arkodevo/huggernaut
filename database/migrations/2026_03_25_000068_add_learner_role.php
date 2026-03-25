<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update existing learner account
        DB::table('users')
            ->where('email', 'chuluoyi84@gmail.com')
            ->update(['role' => 'learner']);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('email', 'chuluoyi84@gmail.com')
            ->update(['role' => 'user']);
    }
};
