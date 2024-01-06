<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use App\Http\Controllers\FeedController;
use App\Models\UserSource;
use App\Models\Source;
use Carbon\Carbon;

class ExportController extends Controller
{

    protected $allowedType = ['category','source','tag','feed'];
    protected $feedController = null;
    protected $userFeedController = null;

    public function __construct() {
        $this->feedController = new FeedController();
        $this->userFeedController = new UserFeedController();
    }

    public function exportFeedsContentFromSource(Request $request,$userSourceId)
    {
        $userId = $request->user()->id;
        $userSource = UserSource::where('id',$userSourceId)->where('user_id',$userId)
                      ->get()->first();
        $feedsContentFromSource = null;

        if($userSource){
            $feedsContentFromSource = Source::where('id',$userSource->source_id)
                        ->with('feeds')->get()->first();
            $this->exportToWord($feedsContentFromSource);
        }else{
            $this->error('User did not own this source:'.$userSourceId);
        }
        return $feedsContentFromSource;
    }

    public function exportToWord2(Request $request,$type='source',$selectedFeeds=null)
    {
        
        $phpWord = new PhpWord();

        // if($type!=null){
        //     if(in_array($type,$this->allowedType)){
        //         switch($type){
        //             case $type=='category' && isset($request->category_id):
        //                 $feeds = $this->getFeedsFromCategory($request->category_id);
        //                 break;
        //             case $type=='source' && isset($request->source_id):
        //                 $feeds = $this->getFeedsFromSource($request->source_id);
        //                 break;
        //             case $type=='tag' && isset($request->tag_id):
        //                 $feeds = $this->getFeedsFromTag($request->tag_id);
        //                 break;
        //             case $type=='feed' && isset($request->feed_id):
        //                 $feeds = $this->getFeedsFromCategory($request->feed_id);
        //                 break;
        //             case $type==null:
        //                 $feeds = $this->getAllFeeds($request->user()->id);
        //         }
        //     }else{
        //         return $this->error('The type is not supported:'.$type);
        //     }
        // }
    
        $section = $phpWord->addSection();

        foreach ($feeds as $feed) {
            // $feedContent = $this->feedController->getUserFeed($request,$feed->id);
            $section->addText($feed['title']);
            $section->addText($feed['description']);
            // Add other feed details as needed
            $section->addText(''); // Add a blank line between feeds
        }

        $filename = 'exported_feeds.docx';

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($filename);

        return response()->download($filename)->deleteFileAfterSend(true);
    }

    public function exportToWord($contents)
    {
        $phpWord = new PhpWord();
        $itemSection = $phpWord->addSection();
        if($contents==null){
            return $this->error('No feeds able to export');
        }
        $sourceTitle = $itemSection->addText('Title: '.$contents['title']);
        $sourceRssTitle = $itemSection->addLink('RSS Url: '.$contents['rss_url']);
        $sourceDesc = $itemSection->addText('Desc: '.$contents['description']);
        // $sourceMetaData = $titleSection->addText($contents['metadata']);

        // dd($items);
        foreach ($contents->feeds as $item) {
            // dd($item);
            // $feedContent = $this->feedController->getUserFeed($request,$feed->id);
            $itemSection->addText($item['title']);
            $itemSection->addText($item['description']);
            $itemSection->addText($item['clean_content']);
            $itemSection->addLink($item['link']);
            $itemSection->addText($item['pubdate']);
            // $itemSection->addText($item['authors']);
            // Add other feed details as needed
            $itemSection->addText(''); // Add a blank line between feeds
        }

        $datetime = now()->format('Y-m-d_H-i-s');

        $filename = 'exported_feeds_'.$datetime.'.docx';

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($filename);

        return response()->download($filename)->deleteFileAfterSend(true);
    }

    public function getFeedsFromCategory(Request $request,$categoryId)
    {
        return $this->userFeedController->getUserFeedsWithCategory($request,$categoryId); 
    }

    public function getFeedsFromSource($sourceId)
    {

    }

    public function getFeedsFromTag($tagId)
    {

    }

    public function getAllFeeds($userId)
    {

    }
}
