<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conferences', function (Blueprint $table) {
            $table->id();
            $table->time('start_time')->default('09:00');
            $table->unsignedSmallInteger('presentation_minutes')->default(15);
            $table->unsignedSmallInteger('break_minutes')->default(20);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conferences');
    }
};

