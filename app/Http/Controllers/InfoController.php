<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Info;
use App\Http\Requests\InfoType\AddInfoTypeRequest;
use App\Http\Requests\InfoType\UpdateInfoTypeRequest;

class InfoController extends Controller
{
    // protected $contentType = ['category', 'tag','list','note'];

    public function getCategoryList(Request $request){
        return $this->getInfoTypeList($request,'category');
    }

    public function getCategoryDetail(Request $request,$infoTypeId){
        return $this->getInfoTypeDetail($request,$infoTypeId,'category');
    }

    public function addCategory(AddInfoTypeRequest $request){
        // dd($request->validated());
        return $this->addInfo($request,'category');
    }
    public function updateCategory(UpdateInfoTypeRequest $request,$infoTypeId){
        return $this->updateInfo($request,$infoTypeId,'category');
    }
    public function deleteCategory(Request $request,$infoTypeId){
        return $this->deleteInfo($request,$infoTypeId,'category');
    }

    public function getTagList(Request $request){
        return $this->getInfoTypeList($request,'tag');
    }

    public function getTagDetail(Request $request,$infoTypeId){
        return $this->getInfoTypeDetail($request,$infoTypeId,'tag');
    }

    public function addTag(AddInfoTypeRequest $request){
        return $this->addInfo($request,'tag');
    }

    public function updateTag(UpdateInfoTypeRequest $request,$infoTypeId){
        return $this->updateInfo($request,$infoTypeId,'tag');
    }

    public function deleteTag(Request $request,$infoTypeId){
        return $this->deleteInfo($request,$infoTypeId,'tag');
    }

    private function getInfoTypeList(Request $request,$infoType){
        $userId = $request->user()->id;
        if(!$this->isAllowedInfoType($infoType)){
            $this->error('The info type is not supported: '.$infoType.'.');
        }else{
            $infoTypeList = Info::where(['user_id'=>$userId,
                                         'type'=>$infoType])->get();
            return $this->success(['info'=>$infoTypeList]);
        }
    }

    private function getInfoTypeDetail(Request $request,$infoTypeId,$infoType){
        $userId = $request->user()->id;
        // $request->validated();
        if(!$this->isAllowedInfoType($infoType)){
            return $this->error('The info type is not supported: '.$infoType.'.');
        }else{
            $infoType = Info::where(['user_id'=>$userId,
                                         'type'=>$infoType,
                                         'id'=>$infoTypeId])->get();
            return $infoType==null?$this->error('The user did not own this '.$infoType.'.'):$this->success(['info'=>$infoType]);
        }
    }

    private function addInfo(AddInfoTypeRequest $request,$infoType){
        $info = new Info();
        $allowedInfoTypes = $info->getAllowedInfoType();
        $userId = $request->user()->id;
        if(!in_array($infoType, $allowedInfoTypes)){
            $this->error('The info type is not supported: '.$infoType);
        }else{
            $info = Info::updateOrCreate([
                'user_id' => $userId,
                'type' => $infoType,
                'title' => $request->title
            ],[
                'description'=>$request->description,
                'references'=>$request->references,
            ]);
            return $this->success([
                'info' => $info
            ]);
        }
    }

    private function updateInfo(UpdateInfoTypeRequest $request,$infoTypeId,$infoType){
        $userId = $request->user()->id;
        $info = Info::where(['id'=>$infoTypeId,'user_id'=>$userId])->get()->first();
        if(!$this->isAllowedInfoType($infoType)){
            return $this->error('The info type is not supported: '.$infoType.'.');
        }elseif($info==null){
            return $this->error('This user did not own this '.$infoType.'.');
        }else{
            $info = Info::updateOrCreate([
                'id' => $infoTypeId,
                'user_id' => $userId,
                'type' => $infoType,
            ],[
                'title' => $request->title,
                'description'=>$request->description,
                'references'=>$request->references,
            ]);
            return $this->success([
                'info' => $info
            ]);
        }
    }

    private function deleteInfo(Request $request,$infoTypeId,$infoType){
        $userId = $request->user()->id;
        $info = Info::where(['id'=>$infoTypeId,'user_id'=>$userId])->get()->first();

        if(!$this->isAllowedInfoType($infoType)){
            return $this->error('The info type is not supported: '.$infoType.'.');
        }elseif($info==null){
            return $this->error('This user did not own this '.$infoType.'.');
        }else{
            $info->delete();
            return $this->success([
                'message' => 'This '.$infoType.' is deleted.'
            ]);
        }
    }

    private function isAllowedInfoType($infoType){
        $info = new Info();
        $allowedInfoTypes = $info->getAllowedInfoType();
        return in_array($infoType, $allowedInfoTypes);
    }

    public function isUserHasInfoType($userId,$infoType,$infoId):bool
    {
        $hasInfoType = false;
        if (!$this->isAllowedInfoType($infoType)) {
            return $hasInfoType;
            // return $this->error('The info type is not supported:'.$infoType.'.');
        }else{
            $userInfo = Info::where(['user_id'=>$userId,'type'=>$infoType,'id'=>$infoId])->first();
            $hasInfoType = $userInfo!=null?true:false;
            return $hasInfoType;
            // return $hasInfoType==true? $this->success([
            //     'has_info_type' => $hasInfoType,
            //     'info' => $userInfo,
            // ]):$this->error([
            //     'has_info_type' => $hasInfoType,
            //     'message' => 'User did not own this '.$infoType,
            // ]);
        }
    }

    public function getInfoEntryFromInfoType(Request $request,$origin,$infoType,$infoId){
        $userId = $request->user()->id;
        $infoWithInfoEntry = null;
        $info = Info::where('id',$infoId)->where('user_id',$userId)
                ->where('type',$infoType)->first();
        
        if($origin=='source' || $origin=='feed'){
            $infoWithInfoEntry = Info::with($origin)
                        ->where('id',$infoId)
                        ->where('user_id',$userId)
                        ->get(); 
        }else{
            $this->error('This origin is not supported:'.$origin);
        }
        return $this->success([$infoType=>$infoWithInfoEntry]);
        
    }
}