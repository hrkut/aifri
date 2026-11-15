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
        // Public pages show data (date, deadlines, Teams link) of the current edition.
        View::composer(['home', 'registration', 'program', 'committee', 'question', 'recordings.login', 'recordings.list'], function ($view) {
            $view->with('conference', Conference::current());
        });
    }
}
