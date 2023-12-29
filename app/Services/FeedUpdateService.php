<?php

namespace App\Services;

use App\Models\Feed;
use App\Models\User;
use App\Models\UserFeed;
use App\Repositories\FeedRepository;
use App\Repositories\UserFeedRepository;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\CrawlerController;
use App\Models\Source;

class FeedUpdateService
{
    public function updateFeeds($sourceId)
    {
        $source = Source::find($sourceId)->first();
        $crawler = new CrawlerController();
        $rssItemsData = $crawler->readRssItems($source->url);
        $feedController = new FeedController();
        $lastFetchedFeedId = null;

        foreach($rssItemsData as $rssItem){
            $lastFetchedFeedId = $feedController->addOrUpdateFeed($sourceId,$rssItem);
        }

        return $lastFetchedFeedId;
    }
}