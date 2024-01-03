<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\UpdateFeedsJob;

class UpdateFeeds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feeds:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $updateFeedJobs =  new UpdateFeedsJob();
        $updateFeedJobs->handle();
        return Command::SUCCESS;
    }
}
