<?php

use App\Jobs\CleanTemporaryUploadsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule: Limpiar archivos temporales cada hora
Schedule::job(new CleanTemporaryUploadsJob)->hourly();
