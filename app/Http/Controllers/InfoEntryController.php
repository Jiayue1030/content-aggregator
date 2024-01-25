<?php

namespace App\Http\Controllers;

use App\Models\InfoEntry;
use Illuminate\Http\Request;
use App\Models\UserFeed;
use App\Models\UserSource;
use App\Models\Info;
use App\Http\Controllers\InfoController;
use App\Http\Requests\InfoEntry\AddInfoEntryRequest;
use App\Http\Requests\InfoEntry\DeleteInfoEntryRequest;
use App\Http\Requests\InfoType\DeleteInfoTypeRequest;

class InfoEntryController extends Controller
{
    public function addSourceToCategory(AddInfoEntryRequest $request){
        if(isset($request['source_id'])){
            if(isset($request['category_id'])){
                $userCategoryId = $request['category_id'];
                $userSourceId = $request['source_id'];
                return $this->addOriginToInfoType($request,'category',$userCategoryId,'source',$userSourceId);
            }else{
                return $this->error('At least one category is needed.');
            }
        }else{
            return $this->error('At least one source is needed.');
        }
    }

    public function addSourceToTag(AddInfoEntryRequest $request,$userSourceId,$userTagId){
        return $this->addOriginToInfoType($request,'tag',$userTagId,'source',$userSourceId);
    }

    public function addFeedToCategory(AddInfoEntryRequest $request,$userFeedId,$userCategoryId){
        return $this->addOriginToInfoType($request,'category',$userCategoryId,'feed',$userFeedId);
    }

    public function addFeedToTag(AddInfoEntryRequest $request,$userFeedId,$userTagId){
        return $this->addOriginToInfoType($request,'tag',$userTagId,'feed',$userFeedId);
    }

    public function deleteSourceFromCategory(DeleteInfoEntryRequest $request,$infoEntryId){
        return $this->deleteInfoEntry($request,$infoEntryId,$infoType='category',$origin='source');
    }

    public function deleteFeedFromCategory(DeleteInfoEntryRequest $request,$infoEntryId){
        return $this->deleteInfoEntry($request,$infoEntryId,$infoType='category',$origin='feed');
    }

    public function deleteSourceFromTag(DeleteInfoEntryRequest $request,$infoEntryId){
        return $this->deleteInfoEntry($request,$infoEntryId,$infoType='tag',$origin='source');
    }

    public function deleteFeedFromTag(DeleteInfoEntryRequest $request,$infoEntryId){
        return $this->deleteInfoEntry($request,$infoEntryId,$infoType='tag',$origin='feed');
    }

    //origin = ['source','feed]; infotype=['category','tag']
    //From an info type(category,tag), get the list of origins(sources,feeds)
    public function getOriginFromInfoType(Request $request,$origin,$infoType,$infoTypeId){
        $originList = null;
        if(!($infoType == 'category' || $infoType=='tag')){
            return $this->error('This info type is not supported:'.$infoType);
        }
        if($origin=='source'){
            $originList = InfoEntry::with('info')
                            ->with('sources')
                            ->where('user_id',$request->user()->id)
                            ->where('type_id',$infoTypeId)->get();
        }elseif($origin=='feed'){
            $originList =  InfoEntry::with('info')
                            ->with('feeds')
                            ->where('user_id',$request->user()->id)
                            ->where('type_id',$infoTypeId)->get();
            // $originList = $originList->pluck('category.info','feeds');
        }else{
            return $this->error('This origin type is not supported:'.$origin);
        }
        return $this->success([$infoType => $originList]);
        
    }

    //From an origin(sources/feeds),get a list of infotype(category/tag)
    //Get a feed details with sources,categories,tags
    //Example: 'feed/get/1' 'source/get/1'
    public function getOriginDetails(Request $request,$originType,$originId){
        $origin = null;
        $userId = $request->user()->id;
        if($originType == 'source'){
            $origin = UserSource::where('id',$originId)
                ->with('source')
                ->with('categories')
                ->with('tags')
                ->where('user_id',$userId)->first();
            // $categories = $origin::with('categories');
            // $tags = '';
            return $origin;
        }elseif($originType == 'feed'){
            $origin = UserFeed::where('id',$originId)
                ->with('feed')
                ->with('categories')
                ->with('tags')
                ->where('user_id',$userId)->first();
            return $origin;
        }

        // else{
        //     return $this->error('This origin type is not supported:'.$origin);
        // }
        // return $this->success([$origin => $originList]);
        
    }

    //Add a source(origin) into category(infoType) as InfoEntry
    // return $this->addOriginToInfoType($request,'category',$userCategoryId,'source',$userSourceId);
    public function addOriginToInfoType($data,$infoType,$infoTypeId,$origin,$originIds)
    {
        $isAllowedOrigin = $this->isAllowedOrigin($origin);
        $userId = $data->user()->id;
        // dd($data->all());
        if($isAllowedOrigin){
            foreach($originIds as $originId){
                // echo($originId);
                $isUserHasOrigin = $this->isUserHasOrigin($userId,$origin,$originId);
                if($isUserHasOrigin){
                    $isUserHasInfoType = $this->isUserHasInfoType($userId,$infoType,$infoTypeId);
                    if($isUserHasInfoType){
                        $infoEntry = InfoEntry::updateOrCreate([
                            'user_id' => $userId,
                            'origin' => $origin,
                            'origin_id' => $originId,
                            'type' => $infoType,
                            'type_id' => $infoTypeId,
                        ],[
                            'title'=> $data->title,
                            'description'=> $data->description,
                            'contents' => $data->contents
                        ]);
                        // return $this->success([
                        //     'info_entry' => $infoEntry,
                        //     'message' => 'The '.$origin.' is added to '.$infoType.'.'
                        // ]);
                    }else{
                        return $this->error('This user did not own this '.$infoType.'.');
                    }
                }
            }
            // else{
            //     return $this->error('This user did not own this '.$origin.'.');
            // }
        }else{
            return $this->error('The origin is not allowed:'.$origin.'.');
        }
        
    }

    //Remove a source(origin) from category(infoType)
    private function deleteInfoEntry(DeleteInfoEntryRequest $request,$infoEntryId,$infoType,$origin){
        //infoEntry is the relationship record between origin(source,feed) and infoType(category,tag)
        $userId = $request->user()->id;
        $infoEntry = InfoEntry::where(['id'=>$infoEntryId,'user_id'=>$userId])->get()->first();

        if($infoEntry==null){
            return $this->error('This user did not own this info entry');
        }else{
            $infoEntry->delete();
            return $this->success([
                'message' => 'This '.$infoType.' is deleted from '.$origin.'.'
            ]);
        }
    }

    private function isUserHasOrigin($userId,$origin,$originId){
        $hasOrigin = false;
        if($this->isAllowedOrigin($origin)){
            if($origin == 'feed'){
                $userFeed = UserFeed::where(['id'=>$originId,'user_id'=>$userId])->first();
                $hasOrigin = $userFeed==null?false:true;
            }elseif($origin == 'source'){
                $userSource = UserSource::where(['id'=>$originId,'user_id'=>$userId])->first();
                $hasOrigin = $userSource==null?false:true;
            }else{
                return $hasOrigin;
            }
        return $hasOrigin;
        }
    }

    private function isUserHasInfoType($userId,$infoType,$infoId){
        $infoController = new InfoController();
        return $infoController->isUserHasInfoType($userId,$infoType,$infoId);
    }

    private function isAllowedOrigin($infoType):bool
    {
        $infoEntry = new InfoEntry();
        $allowedOrigin = $infoEntry->getAllowedOrigin();
        return in_array($infoType, $allowedOrigin);
    }
}