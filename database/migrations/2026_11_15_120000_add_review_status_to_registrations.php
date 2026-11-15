<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Review of submitted talks (active participation): pending / approved / rejected,
 * with the reason sent to the author on rejection. Only approved talks are part of the programme.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->string('review_status', 16)->nullable()->after('participation_type');
            $table->timestamp('reviewed_at')->nullable()->after('review_status');
            // explanation sent to the author when a talk is rejected
            $table->text('review_comment')->nullable()->after('reviewed_at');
        });

        // Talks that already exist were accepted the old way (they are in the programme).
        DB::table('registrations')
            ->where('participation_type', 'presentation')
            ->whereNull('review_status')
            ->update(['review_status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['review_status', 'reviewed_at', 'review_comment']);
        });
    }
};
