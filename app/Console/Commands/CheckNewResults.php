<?php

namespace App\Console\Commands;

use App\Services\XmlDataService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckNewResults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check-new-results';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if new result file have been created since the last check';

    private XmlDataService $xmlService;

    public function __construct(XmlDataService $xmlService)
    {
        parent::__construct();
        $this->xmlService = $xmlService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::channel('seek_and_stock_process')->info('Start seek_and_stock');
        $files = glob('to_del/*result*3*.xml');
        Log::channel('seek_and_stock_process')->info(count($files) . ' files found.');
        foreach ($files as $file) {
//            if (filemtime($file) > strtotime('-1 minute')) { //TODO: remettre en place une fois sur le serveur + tests
                $this->xmlService->processXmlFile($file);
//            }
        }
        Log::channel('seek_and_stock_process')->info('Ending seek_and_stock');

    }
}
