<?php

namespace App\Http\Controllers;

use App\Jobs\ExtractArticleJob;
use Illuminate\Http\Request;
use App\Models\Feed;
use App\Models\Source;
use App\Models\UserFeed;
use App\Services\FeedService;
use App\Models\UserSource;

class FeedController extends Controller
{
    //Add feeds from the source                                    
    public function addFeeds(Request $request,$sourceId)
    {
        $userId = $request->user()->id;
        $crawler = new CrawlerController();
        $rssUrl = urldecode($request->url);
        $rssResults = $crawler->readRssItems($rssUrl);

        // Get the RSS results which are 100 days earlier than the request date
        // $filteredResults = $this->filterRssItemsByDate($rssResults, 100);

        // Iterate through the filtered results and add them to the Feed model and user_feeds table
        foreach ($rssResults as $rssItem) {
            // Check if the feed already exists, and if not, add it
            $feed = Feed::updateOrCreate(
                ['guid' => $rssItem['guid'],
                 'link' => $rssItem['link'],
                ],
                [
                    'title' => $rssItem['title'],
                    'description' => $rssItem['description'],
                    'content' => $rssItem['content'],
                    'link' => $rssItem['link'],
                    'guid' => $rssItem['guid'],
                    'pubdate' => $rssItem['pubdate'],
                    'categories' => $rssItem['categories'], 
                    'authors' => $rssItem['authors'], 
                    'source_id' => $sourceId, 
                ]
            );

            //Dispatch the ExtractArticleJob
            ExtractArticleJob::dispatch($feed,$rssItem['link']);

            UserFeed::updateOrCreate(
                ['user_id' => $userId,'feed_id' => $feed->id,'source_id'=>$sourceId],
                ['updated_at' => now()]
            );
        }

        return response()->json(['message' => 'Feeds added successfully.']);
    }

    public function getUserFeedsList(Request $request){
        // dd($request->user());
        $sourceId = isset($request->source_id)?$request->source_id:null;
        $userFeedsList = [];
        if($sourceId!=null){
            $userSource = UserSource::where('source_id',$sourceId)->first();
            $source = Source::where('id',$sourceId)->first();
            if($source){
                // $userFeedIds = UserFeed::where('user_id',$request->user()->id)
                //         ->where('source_id',$sourceId)
                //         ->get('feed_id');
                // $userFeedsList = Feed::whereIn('id',$userFeedIds)->with('source')
                // ->orderBy('pubdate','desc')->get();
                
                $userFeedsList = Feed::where('source_id',$source->id)->with('source')
                ->orderBy('pubdate','desc')->get();

                if($source==null){
                    return $this->error('Source not exist');
                }
            }else{
                return $this->error('User did not own this source');
            }
        }else{
            $userSourceIds = UserSource::where('user_id',$request->user()->id)->get('source_id');
            $sourceIds = Source::whereIn('id',$userSourceIds)->get('id');

            $userFeedIds = UserFeed::where('user_id',$request->user()->id)
                        ->whereIn('source_id',$sourceIds)
                        ->get('feed_id');
            $userFeedsList = Feed::whereIn('id',$userFeedIds)->with('source')
            ->orderBy('pubdate','desc')->get();
        }
        return $this->success(['feeds'=>$userFeedsList]);
    }

    public function getUserFeed(Request $request,$userFeedId){
        $userId = $request->user()->id;
        $userFeed = UserFeed::where([
            'user_id' => $userId,
            'id'      => $userFeedId
        ])->first();
        $userFeedDetail = null;

        if($userFeed){
            $userFeedDetail = UserFeed::with('feed')
            ->where(['user_id' => $request->user()->id,
                     'feed_id' => $userFeed->feed_id
            ])->get();

            return $this->success([
                'response' => $userFeedDetail
            ]);
        }else{
            return $this->error('User Feed entry did not exist');
        }
    }

    public function updateFeed(Request $request,$userFeedId){
        $userId = $request->user()->id;
        $userFeed = UserFeed::where(['user_id'=>$userId,'id'=>$userFeedId])->find($userFeedId);
        $attributesToUpdate = $request->only(['references', 'status', 'is_read', 'is_star']);
        
        if($userFeed){
            $userFeed->update($attributesToUpdate);
            
            return $this->success([
                'message' => 'User Feed entry updated.'
            ]);
        }else{
            return $this->error('User Feed entry did not exist');
        }
    }

    public function deleteFeed(Request $request,$userFeedId){
        $userId = $request->user()->id;
        $userFeed = UserFeed::where(['user_id'=>$userId,'id'=>$userFeedId])->find($userFeedId);
        if($userFeed){
            $userFeed->status = 'disabled';
            $userFeed->delete();
            return $this->success([
                'message' => 'Remove Feed.'
            ]);
        }else{
            return $this->error('User Feed entry did not exist');
        }
    }

    //A function to let users get filtered rss items
    //daysago -> will get rss items within (daysago -> current date)
    public function filterRssItemsByDate($rssItems, $daysAgo=1,$date=null)
    {
        $filteredResults = [];

        // Assuming $rssItem->pubDate contains the publication date
        $currentDate = now();
        foreach ($rssItems as $rssItem) {
            // $carbonDate = \Carbon\Carbon::createFromFormat('j F Y, g:i a', $rssItem['pubdate']);
            // $pubDate = $carbonDate->toDateTimeString();
            
            $pubDate = $rssItem['pubdate'] ;
            $pubDate = \Carbon\Carbon::parse($pubDate);
            // dd($pubDate);
            // Check if the item's publication date is within the specified range
            if ($pubDate->diffInDays($currentDate) <= $daysAgo) {
                $filteredResults[] = $rssItem;
            }
        }
        return $filteredResults;
    }

    public function filterUpdatedFeeds($rssItems, $requestPubDate)
    {
        $filteredResults = [];
        // dd($rssItems);
        foreach ($rssItems as $rssItem) {
            $itemPubDate =  $rssItem['pubdate'];
            // Check if the item's pubdate is greater than the request pubdate
            if ($itemPubDate > $requestPubDate) {
                $filteredResults[] = $rssItem;
            }
        }

        return $filteredResults;
    }

    //Find the feed from a source id
    public function addOrUpdateFeed($sourceId,$rssItem){
        $existingFeed = Feed::where('guid',$rssItem['guid'])
                        ->where('source_id',$sourceId)
                        ->orderBy('pubdate','desc')
                        ->get()->first();
        $newFeed = null;
        if(!$existingFeed){ //If the feeds not exists
            $newFeed = Feed::updateOrCreate(
                [   'guid' => $rssItem['guid'],
                    'source_id' => $sourceId,
                    'link' => $rssItem['link'],
                ],
                [
                    'title' => $rssItem['title'],
                    'description' => $rssItem['description'],
                    'content' => $rssItem['content'],
                    'pubdate' => $rssItem['pubdate'],
                    'categories' => $rssItem['categories'], 
                    'authors' => $rssItem['authors'], 
                    'source_id' => $sourceId, 
                ]
            );
            $this->addFeedToSubscribedUsers($newFeed->id,$sourceId);
        }else{
            $this->addFeedToSubscribedUsers($existingFeed->id,$sourceId);
        }
        $result = $existingFeed==null?$newFeed->id:$existingFeed->id;
        // dd($result);
        return $result;
    }

    private function addFeedToSubscribedUsers($feedId,$sourceId){
        $users = UserSource::where('source_id',$sourceId)->get();
        foreach($users as $user){
            UserFeed::updateOrCreate(
                ['user_id' => $user->id,
                'feed_id' => $feedId,
                'source_id'=>$sourceId],
                ['updated_at' => now()]
            );
        }
    }

    //To get add all related Feeds from the Source to UserFeed 
    public function addFeedsToUsers($sourceId,$feedId=null,$userId=null){
        $source = Source::find($sourceId)->first();
        $addedUserFeed = null;
        if($feedId != null) //Add this particular feeds to the User or All Subscibed Users
        {
            $feed = Feed::find('id',$feedId)->first();
            if($userId!=null){ //Add this particular feed to the UserFeed with userId
                UserFeed::updateOrCreate(['user_id'=>$userId,'feed_id'=>$feedId,'source_id'=>$sourceId]);
            }else{ //Add this particular feed to the UserFeed with all subscribed users
                $allSubscribedUsers = $this->getAllSubscribedUsers($sourceId);
                foreach($allSubscribedUsers as $user){
                    UserFeed::updateOrCreate(['user_id'=>$user->id,'feed_id'=>$feedId,'source_id'=>$sourceId]);
                }
            }
        }else{ //Get all feeds from the source, and add all feeds to all subscribed users
            $feeds = Feed::where('source_id',$sourceId)->get();
            if($feeds){ //If this Source has Feeds
                if($userId==null){
                    $allSubscribedUsers = $this->getAllSubscribedUsers($sourceId);
                    foreach($feeds as $feed){
                        foreach($allSubscribedUsers as $user){
                            UserFeed::updateOrCreate(['user_id'=>$user->id,'feed_id'=>$feed->id,'source_id'=>$sourceId]);
                        }
                    } 
                }else{
                    foreach($feeds as $feed){
                        // echo('Add this feed with id:'.$feed->id.'to user'.$userId.'\n');
                        UserFeed::updateOrCreate(['user_id'=>$userId,'feed_id'=>$feed->id,'source_id'=>$sourceId]);
                    }
                }
            }
        }
    }

    private function getAllSubscribedUsers($sourceId){
        // $source = Source::find('id',$sourceId)->first();
        $subscribedUsers = UserSource::where('source_id',$sourceId)->get('user_id');
        return $subscribedUsers;
    }
    
}

/**
 * 
 * 【】when add an existing source, how to add those feeds into user_feed sekali?
 * 【】updatefeedsjob
 * 【】export
 */
