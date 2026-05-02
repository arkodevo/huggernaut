<?php

namespace App\Providers;

use App\Models\WordPronunciation;
use App\Models\WordSenseExample;
use App\Observers\WordPronunciationObserver;
use App\Observers\WordSenseExampleObserver;
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
        WordPronunciation::observe(WordPronunciationObserver::class);
        WordSenseExample::observe(WordSenseExampleObserver::class);
    }
}
