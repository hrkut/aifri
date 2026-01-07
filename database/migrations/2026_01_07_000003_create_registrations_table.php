<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 100)->nullable();
            $table->string('institution');
            $table->string('position')->nullable();
            $table->string('participation_type', 32);
            $table->string('title')->nullable();
            $table->text('abstract')->nullable();
            $table->string('keywords')->nullable();
            $table->string('block', 32)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};

