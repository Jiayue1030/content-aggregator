<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\InfoEntryController;
use App\Models\InfoEntry;
use App\Models\Info;
use App\Models\UserFeed;

class UserFeedController extends Controller
{
    //To get the list of feeds which is with category(infoType) with infoId=1
    /**
     * select * from feeds f join user_feeds uf on f.id=uf.feed_id
     * join infos i on i.user_id=uf.user_id and i.type='category'
     * join info_entries ie on ie.type_id=i.id and uf.feed_id = ie.origin_id
     */
    public function getUserFeedsWithCategory(Request $request,$categoryId,$infoType='category'){
        $userId = $request->user()->id;
        // $userCategory = Info::where('type',$infoType)->where('user_id',$userId)->get();
        // $feedsWithCategory = InfoEntry::where('type_id',$categoryId)->where('origin','feed')
        //                      ->where('user_id',$userId)->get();
        $userFeedsWithInfoType = UserFeed::with('categories')
        ->where('user_id', $userId)
        ->get();
        return $userFeedsWithInfoType;
    }
}
