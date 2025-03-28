<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SeekAndStockJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $xmlService;

    // Injecter le service XML via le constructeur
    public function __construct($xmlService)
    {
        $this->xmlService = $xmlService;
    }

    /**
     * Exécuter le Job.
     */
    public function handle(): void
    {
        Log::channel('seek_and_stock_process')->info('Start seek_and_stock');

        $files = glob(config('custom.results_path_docker') . '/mxbt-*.xml');

        Log::channel('seek_and_stock_process')->info(count($files) . ' files found.');

        $oldResultsDir = config('custom.results_path_docker') . '/oldResults';
        if (!file_exists($oldResultsDir)) {
            mkdir($oldResultsDir, 0755, true);
            Log::channel('seek_and_stock_process')->info("Directory '$oldResultsDir' created.");
        }

        foreach ($files as $file) {
            try {
                $this->xmlService->processXmlFile($file);

                $destinationPath = $oldResultsDir . '/' . basename($file);
                if (rename($file, $destinationPath)) {
                    Log::channel('seek_and_stock_process')->info("File '$file' moved to '$destinationPath'.");
                } else {
                    Log::channel('seek_and_stock_process')->warning("Failed to move file '$file' to '$destinationPath'.");
                }

            } catch (\Exception $e) {
                Log::channel('seek_and_stock_process')->error("Error processing file '$file': " . $e->getMessage());
            }

        }
        Log::channel('seek_and_stock_process')->info('Ending seek_and_stock');

    }
}
