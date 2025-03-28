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
    ->withoutOverlapping();
