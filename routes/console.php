<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Reset SEO settings to default values.
Artisan::command('settings:seo:reset', function () {
    $this->info('Resetting SEO settings to default values...');
    $seoSettings = app(\App\Settings\SeoSettings::class);
    $seoSettings->reset();
    $this->info('Done! SEO settings reset to default values.');
})->purpose('Reset SEO settings to default values');
