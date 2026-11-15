<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Converts the single "conferences" settings row into yearly editions (ročníky).
 *
 * - conferences get date / deadlines / Teams link / is_active
 * - registrations and questions get conference_id
 * - existing data is assigned to the 2026 edition (11. 2. 2026)
 * - a new active 2027 edition (2. 2. 2027) is created
 */
return new class extends Migration
{
    private const TEAMS_2026 = 'https://teams.microsoft.com/l/meetup-join/19%3ameeting_YjliNGIxN2MtZjA0OC00MjgyLWJmYjQtMjYzMzIwMDZjNGZm%40thread.v2/0?context=%7b%22Tid%22%3a%228324ff4b-14c8-4bf5-b07e-a0713179f37e%22%2c%22Oid%22%3a%224d479202-a42c-46e9-b2f3-e8b1c1583ae9%22%7d';

    public function up(): void
    {
        Schema::table('conferences', function (Blueprint $table) {
            $table->unsignedSmallInteger('year')->nullable()->unique()->after('id');
            $table->date('date')->nullable()->after('year');
            $table->date('registration_deadline')->nullable()->after('date');
            $table->date('notification_date')->nullable()->after('registration_deadline');
            $table->text('teams_url')->nullable()->after('notification_date');
            $table->boolean('is_active')->default(false)->after('teams_url');
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->foreignId('conference_id')->nullable()->after('id')->constrained('conferences')->restrictOnDelete();
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('conference_id')->nullable()->after('id')->constrained('conferences')->restrictOnDelete();
        });

        $now = now();

        // 2026 edition: reuse the existing settings row (start time, durations) if there is one.
        $existing = DB::table('conferences')->orderBy('id')->first();
        $edition2026 = [
            'year' => 2026,
            'date' => '2026-02-11',
            'registration_deadline' => '2026-02-09',
            'notification_date' => '2026-02-07',
            'teams_url' => self::TEAMS_2026,
            'is_active' => false,
            'updated_at' => $now,
        ];

        if ($existing) {
            DB::table('conferences')->where('id', $existing->id)->update($edition2026);
            $id2026 = $existing->id;
            $startTime = $existing->start_time;
            $presentationMinutes = $existing->presentation_minutes;
            $breakMinutes = $existing->break_minutes;
        } else {
            $startTime = '09:00';
            $presentationMinutes = 15;
            $breakMinutes = 20;
            $id2026 = DB::table('conferences')->insertGetId($edition2026 + [
                'start_time' => $startTime,
                'presentation_minutes' => $presentationMinutes,
                'break_minutes' => $breakMinutes,
                'created_at' => $now,
            ]);
        }

        // Any other legacy rows were never used (the app always read the first one).
        DB::table('conferences')->where('id', '!=', $id2026)->delete();

        DB::table('registrations')->whereNull('conference_id')->update(['conference_id' => $id2026]);
        DB::table('questions')->whereNull('conference_id')->update(['conference_id' => $id2026]);

        DB::table('conferences')->insert([
            'year' => 2027,
            'date' => '2027-02-02',
            'start_time' => '09:00',
            'presentation_minutes' => $presentationMinutes,
            'break_minutes' => $breakMinutes,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        $id2026 = DB::table('conferences')->where('year', 2026)->value('id');

        // Keep only the 2026 data, matching the pre-edition single-conference layout.
        if ($id2026) {
            DB::table('registrations')->where('conference_id', '!=', $id2026)->delete();
            DB::table('questions')->where('conference_id', '!=', $id2026)->delete();
            DB::table('conferences')->where('id', '!=', $id2026)->delete();
        }

        Schema::table('questions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('conference_id');
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('conference_id');
        });

        Schema::table('conferences', function (Blueprint $table) {
            $table->dropUnique(['year']);
            $table->dropColumn(['year', 'date', 'registration_deadline', 'notification_date', 'teams_url', 'is_active']);
        });
    }
};
