<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Feed;
use Illuminate\Support\Facades\Log;
use App\Jobs\ExtractArticleJob;

class ContentExtractionBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //Get all feeds where full_content is null
        $feeds = Feed::where('full_content',null)->get();
        // Log::info($feeds);
        foreach($feeds as $feed ){
            ExtractArticleJob::dispatch($feed,$feed['link']);
            Log::info('ContentExtractionBatchJob:Feed ID=>'.$feed->id);
        }
    }
}
