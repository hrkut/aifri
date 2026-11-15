<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Separate deadline for submitting contributions (active participation);
 * passive registration keeps using registration_deadline.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conferences', function (Blueprint $table) {
            $table->date('contribution_deadline')->nullable()->after('registration_deadline');
        });

        // 2026: contributions were accepted until 4. 2. 2026 (see commit 776b865)
        DB::table('conferences')->where('year', 2026)->whereNull('contribution_deadline')
            ->update(['contribution_deadline' => '2026-02-04']);
    }

    public function down(): void
    {
        Schema::table('conferences', function (Blueprint $table) {
            $table->dropColumn('contribution_deadline');
        });
    }
};
