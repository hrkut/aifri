<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // On production these columns were added by hand before this migration existed,
        // so only add what is missing.
        Schema::table('registrations', function (Blueprint $table) {
            if (! Schema::hasColumn('registrations', 'record')) {
                $table->string('record')->nullable()->after('duration_minutes');
            }
            if (! Schema::hasColumn('registrations', 'presentation')) {
                $table->string('presentation')->nullable()->after('record');
            }
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['record', 'presentation']);
        });
    }
};

