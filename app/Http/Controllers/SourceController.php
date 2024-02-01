<?php

namespace App\Http\Controllers;

use App\Http\Resources\SourceResource;
use App\Http\Resources\UserSourcesResource;
use App\Http\Controllers\CrawlerController;
use App\Http\Resources\UserResource;
use App\Models\Source;
use App\Models\UserFeed;
use App\Models\UserSource;
use App\Repositories\SourceRepository;
use App\Services\SourceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Services\FeedService;
use Illuminate\Console\View\Components\Warn;

class SourceController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param  SourceService  $authService
     * @return void
     */
    public function __construct(private SourceService $sourceService,private FeedService $feedService)
    {
        // $sourceService = new SourceService(new SourceRepository(new Source()));
        // $feedService = new FeedService();
    }

    private function checkSourceExistence($url){
        return Response::json(new SourceResource($url));
    }

    public function addSource(Request $request)
    {
        $feedController = new FeedController();
        $existingSource = $this->checkUrlExistence(urldecode($request->url));
        // dd($existingSource);
        $userId = $request->user()->id;

        if ($existingSource!=null) { // If this source already exists
            // echo('exist');
            $userHasSource = $this->userHasSource($userId, $existingSource->id);

            if (!$userHasSource) { // User did not have this source yet
                $userSource = $this->addUserSource($userId, $existingSource->id);

                $userFeedsFromSource = UserFeed::where('source_id', $existingSource->id)
                    ->where('user_id', $userId)
                    ->exists();

                if ($userFeedsFromSource) { // User already has feeds from the source
                    return $this->success(['message' => 'User already has the feeds.']);
                } else {
                    $feedController->addFeedsToUsers($existingSource->id, null, $userId);
                    return $this->success([
                        'source' => $existingSource,
                        'user_source' => $userSource,
                    ]);
                }
            } else { // The user already has this source, check whether this user has the related feeds
                $userFeedsFromSource = UserFeed::where('source_id', $existingSource->id)
                    ->where('user_id', $userId)
                    ->exists();

                if (!$userFeedsFromSource) { // This user does not have the related feeds
                    $feedController->addFeedsToUsers($existingSource->id, null, $userId);
                    return $this->success(['message' => 'Feeds added.']);
                } else {
                    return $this->error('User already has this source and feed.');
                }
            }
        } else {
            // echo('not exist');
            $newSource = $this->createNewSource($request);
            if($newSource!=null){
                $userSource = $this->addUserSource($userId, $newSource->id);
                $newFeeds = $feedController->addFeeds($request, $newSource->id);

                return $this->success([
                    'source' => $newSource,
                    'user_source' => $userSource?$userSource:null,
                    'feeds' => $newFeeds?$newFeeds:null,
                ]);
            }else{
                return $this->error('URL not support RSS.');
            }
        }
    }


    public function searchUrl(Request $request)
    {
        return $this->readRss($request);
    }

    public function getUserSourceList(Request $request){
        // return UserSource::with('sources')->where([
        //     'user_id' => $request->user()->id,
        // ])->get();

        // $sources = UserSource::with('source')
        // ->with('feeds')
        // ->with('categories')->with('tags')
        // ->where('user_id', $request->user()->id)
        // ->get('sources.source');
        $userSourceIds = UserSource::where('user_id',$request->user()->id)
                        ->get('source_id');
        // var_dump($userSourceIds);
        $sources = Source::with('feeds')
                    //->with('categories')->with('tags')
                    ->whereIn('id',$userSourceIds)->get();

        // Extract only the 'sources' data from the result
        // $sources = $userSources->pluck('sources','id','created_at');
        return $this->success(['sources'=>$sources]);
        // return UserSource::where(['user_id'=>$request->user()->id])->get();
        // return Response::json(new SourceResource($request->user()));
    }

    public function getUserSource(Request $request,$userSourceId){
        $userId = $request->user()->id;
        $userSource = UserSource::where([
            'user_id' => $userId,
            'id'      => $userSourceId
        ])->first();
        $userSourceDetail = null;

        if($userSource){
            $userSourceDetail = UserSource::with('source')
            ->where(['user_id' => $request->user()->id,
                     'source_id' => $userSource->source_id
            ])->get();

            return $this->success([
                'response' => $userSourceDetail
            ]);
        }else{
            return $this->error('User Source entry did not exist');
        }
    }

    public function updateUserSource(Request $request,$userSourceId){
        $userId = $request->user()->id;
        $userSource = UserSource::where(['user_id'=>$userId,'id'=>$userSourceId])->find($userSourceId);
        if($userSource){
            $userSource->update($request->all());
            // $userSource->save();
            // $userSource->delete();
            return $this->success([
                'message' => 'User Source entry updated.'
            ]);
        }else{
            return $this->error('User Source entry did not exist');
        }
    }

    public function deleteUserSource(Request $request,$userSourceId){
        $userId = $request->user()->id;
        $userSource = UserSource::where(['user_id'=>$userId,'id'=>$userSourceId])->find($userSourceId);
        // $userFeedsList = UserFeed::where();
        if($userSource){
            $userSource->status = 'disabled';
            // $userSource->save();
            $userSource->delete();
            return $this->success([
                'message' => 'Unsubscibed source.'
            ]);
        }else{
            return $this->error('User Source entry did not exist');
        }
    }


    private function addUserSource(int $user_id, int $source_id)
    {
        $userSource = new UserSource();
        $userSource->user_id = $user_id;
        $userSource->source_id = $source_id;
        $userSource->created_by = $user_id;
        $userSource->save();

        return $userSource;
    }

    public function readRss(Request $request){
        $url = urldecode($request->url);
        $crawler = new CrawlerController();
        return $crawler->readRss($url);
    }

    private function createNewSource(Request $request)
    {
        $crawler = new CrawlerController();
        $created_by = $request->user()->id;
        $url = urldecode($request->url);
        
        $rssResult = $crawler->readRss($url);
        $sourceData = $rssResult!=null?json_decode($rssResult->content()):$rssResult;
        // dd($sourceData);
        if($sourceData == null){
            // echo('No data fetched from crawler');
            return null;
            // return $this->error('The URL is not rss supported.');
        }else{
            // echo('Got data fetch');
            // dd($sourceData->metadata);
            $newSource = Source::create(
                [
                    'url' => $url,
                    'rss_url' => $sourceData->url,
                    'link' => $sourceData->link,
                    'description' => $sourceData->description,
                    'type' => $sourceData->type,
                    'created_by' => $created_by,
                    'title' => $sourceData->title,
                    'is_rss' => $sourceData->is_rss,
                    'language' => $sourceData->language,
                    'metadata' => $sourceData->metadata,
                    'author' => $sourceData->author,
                ]
            );
            // dd($newSource);
            return $newSource;
        }
        // var_dump($sourceData);
    }

    public function userHasSource(int $user_id, int $source_id): bool
    {
        return UserSource::where('user_id', $user_id)
            ->where('source_id', $source_id)
            ->exists();
    }

    public function checkUrlExistence($url)
    {
        $source = Source::where('url', $url)->get()->first();
        // dd($url,$source);
        return $source;
    }
}
