<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ContentExtractionBatchJob;

class BatchExtractContents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feeds:extract';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Batch extract feeds with null full contents';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $contentExtractionBatchJob = new ContentExtractionBatchJob();
        $contentExtractionBatchJob->handle();
        return Command::SUCCESS;
    }
}
