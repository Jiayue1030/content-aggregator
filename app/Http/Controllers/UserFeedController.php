<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\InfoEntryController;
use App\Models\InfoEntry;
use App\Models\Info;
use App\Models\UserFeed;
use Illuminate\Database\Eloquent\Builder;

class UserFeedController extends Controller
{
    public function getUserFeedsWithFolder(Request $request,$folderId,$infoType='folder'){
        $userId = $request->user()->id;
        // $userFeeds = DB::table('user_feeds')
        //              ->join('feeds','user_feeds.feed_id','=','feeds.id')
        //              ->join('info_entries','info_entries.type_id','=',$folderId)
        //              ->join('info','info.')
        //              ->where('user_feeds.user_id','=',$request->user()->id)
        //              ->where('info.type','=','folder')
        //              ->where('info.id','=',$folderId)
        //              ->select('feeds.*','info.*')
        //              ->get()
        // return $userFeeds;
    }

    public function getUserFeedsFromTags(Request $request,$tagId)
    {
        $userId = $request->user()->id;
        $userFeedsFromTag = UserFeed::with('tags')
        ->where('type_id',$tagId)
        ->where('user_id', $userId)
        ->get();
        return $userFeedsFromTag;
    }
    
    public function getUserFeedsFromSource(Request $request)
    {
        // $source = Source::find($)->get()->first();
        $userId = $request->user()->id;
        $userFeedsFromTag = UserFeed::with('tags')
        ->where('user_id', $userId)
        ->get();
        return $userFeedsFromTag;
    }
}
