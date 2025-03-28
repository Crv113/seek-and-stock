<?php

use App\Jobs\SeekAndStockJob;
use App\Services\XmlDataService;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    $job = new SeekAndStockJob(new XmlDataService());
    $job->handle();
})->name('SeekAndStock')
    ->everyMinute()
    ->withoutOverlapping()
    ->before(function () {
        if (app(\Illuminate\Console\Scheduling\EventMutex::class)->exists(now(), 'SeekAndStock')) {
            Log::channel('seek_and_stock_process')->warning('Overlap détecté : tâche précédente toujours en cours. Nouveau lancement annulé.');
            return false;
        }
        return true;
    });
