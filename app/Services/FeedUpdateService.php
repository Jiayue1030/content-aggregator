<?php

namespace App\Services;

use App\Models\Feed;
use App\Models\User;
use App\Models\UserFeed;
use App\Repositories\FeedRepository;
use App\Repositories\UserFeedRepository;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\CrawlerController;
use App\Models\LastFetched;
use App\Models\Source;
use Illuminate\Support\Carbon;

class FeedUpdateService
{
    public function updateFeeds($sourceId)
    {
        $source = Source::where('id',$sourceId)->get()->first();
        $crawler = new CrawlerController();
        $rssItemsData = $crawler->readRssItems($source->url);
        $feedController = new FeedController();
        $lastFetchedFeedFromSourceId = LastFetched::where('source_id',$source->id)->get()->first();
        $newLastFetchedFeedId = null;
        // dd($lastFetchedFeedFromSourceId);
        if(!$lastFetchedFeedFromSourceId){// This source hasn't fetched any RSS items yet
            foreach ($rssItemsData as $rssItem) {
                $feedId = $feedController->addOrUpdateFeed($sourceId, $rssItem);//Get a feed id back from the function
                $newLastFetchedFeedId = $feedId;
                print('=>'.$newLastFetchedFeedId);
            }
        }else{
            // dd($lastFetchedFeedFromSourceId);
            $lastFetchedFeed = Feed::find($lastFetchedFeedFromSourceId->id)->get()->first();
            // dd($lastFetchedFeed);
            $lastFetchedFeedPubdate = $lastFetchedFeed->pubdate;
            $updatedRssItems = $feedController->filterUpdatedFeeds($rssItemsData,$lastFetchedFeedPubdate);
            // dd($updatedRssItems);
            if($updatedRssItems!=[]){
                echo('Yeah got updates');
                foreach($updatedRssItems as $rssItem){
                    $feedId = $feedController->addOrUpdateFeed($sourceId, $rssItem);
                    $newLastFetchedFeedId = $feedId;
                    print('aa$newLastFetchedFeedId=>'.$newLastFetchedFeedId);
                }
            }else{
                echo('no updatesss');
                $newLastFetchedFeedId = $lastFetchedFeed->id;
                print('abbbb$newLastFetchedFeedId=>'.$newLastFetchedFeedId);

            }
            // Filter the RSS items with pubdate > lastFetchedFeedPubdate
            
            echo('这边的这边的newLastFetchedFeedId'.$newLastFetchedFeedId);
        }

        $newLastFetch = LastFetched::updateOrCreate(
            ['source_id' => $sourceId],
            ['last_fetched_feed_id' => $newLastFetchedFeedId,
            'last_check'=>\Carbon\Carbon::now()]
        );

        return $newLastFetch; 
    }
}