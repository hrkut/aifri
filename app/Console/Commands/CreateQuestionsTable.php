<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class CreateQuestionsTable extends Command
{
    protected $signature = 'app:create-questions-table';
    protected $description = 'Create questions table if it does not exist';

    public function handle(): int
    {
        if (!Schema::hasTable('questions')) {
            Schema::create('questions', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255);
                $table->string('email', 255);
                $table->text('question');
                $table->timestamps();
            });
            $this->info('Questions table created successfully.');
            return 0;
        } else {
            $this->info('Questions table already exists.');
            return 0;
        }
    }
}

