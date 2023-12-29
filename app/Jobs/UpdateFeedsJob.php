<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Source;
use App\Models\LastFetched;
use App\Models\Feed;
use App\Services\FeedUpdateService;

class UpdateFeedsJob implements ShouldQueue
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
        $sources = Source::all();
        $service = new FeedUpdateService();

        foreach ($sources as $source) {
            $sourceId = $source->id;
            $lastFetched = LastFetched::where('source_id', $source->id)->first();
    
            //  To check if the source has been updated since the last check
            if ($this->shouldUpdate($lastFetched)) {
                // Fetch and update feeds for the source
                $newFeeds = $service->updateFeeds($sourceId);
    
                // Update the last_fetched table
                $this->updateLastFetched($source, $newFeeds);
            }
        }
    }

    protected function shouldUpdate($lastFetched)
    {
        if (!$lastFetched) {
            return true; // Source never fetched before
        }

        $latestFeedId = $this->getLatestFeedIdFromSource($lastFetched->source_id);

        return $latestFeedId > $lastFetched->last_fetched_feed_id;
    }

    protected function updateLastFetched($source, $newFeeds)
    {
        // Update the last_fetched table with the latest information
        LastFetched::updateOrCreate(
            ['source_id' => $source->id],
            [
                'last_fetched_feed_id' => optional($newFeeds->last())->id,
                'last_fetched_datetime' => now(),
            ]
        );
    }

    public function getLatestFeedIdFromSource($sourceId=1){
        $latestFeedId = Feed::where('source_id',$sourceId)->latest('id')->first();
        return $latestFeedId;
    }
}
