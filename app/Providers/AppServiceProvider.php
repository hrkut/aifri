<?php

namespace App\Providers;

use App\Models\Conference;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // PDFs (programme, attendance list): embed only the glyphs used instead of the
        // whole DejaVu font – about 80 kB instead of 1.7 MB per file
        config(['dompdf.options.enable_font_subsetting' => true]);

        // Public pages show data (date, deadlines, Teams link) of the current edition.
        View::composer(['home', 'registration', 'program', 'committee', 'question', 'recordings.login', 'recordings.list'], function ($view) {
            $view->with('conference', Conference::current());
        });
    }
}
