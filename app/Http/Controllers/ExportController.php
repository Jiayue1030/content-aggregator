<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
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
        $userSource = UserSource::where('id',$userSourceId)
                      ->where('user_id',$userId)
                      ->get()->first();
        $feedsContentFromSource = null;

        if($userSource){
            $feedsContentFromSource = Source::where('id',$userSource->source_id)
                        ->with('feeds')->get()->first();
            // dd($feedsContentFromSource);
            $this->exportToWord($feedsContentFromSource);
        }else{
            $this->error('User did not own this source:'.$userSourceId);
        }
        return $feedsContentFromSource;
    }

    public function exportFeedsContentFromSource2(Request $request)
    {
        if(!isset($request->user_source_ids)){
            return $this->error('Need at least one source to export feeds!');
        }

        $userSourceIds = isset($request->user_source_ids)?(array)$request->user_source_ids:0;
        $userId = $request->user()->id;
        $feedsContentFromSources = [];
        // dd();
        foreach ($userSourceIds as $userSourceId) {
            $userSource = UserSource::where('id', $userSourceId)
                ->where('user_id', $userId)
                ->first();
            // dd($userSource);
            if ($userSource) {
                $feedsContentFromSource = Source::where('id', $userSource->source_id)
                    ->with('feeds')->first();

                $this->exportToWord($feedsContentFromSource);
                $feedsContentFromSources[] = $feedsContentFromSource;
            } else {
                $this->error('User did not own this source:' . $userSourceId);
            }
        }

        return $feedsContentFromSources;
    }


    public function exportToWord($contents)
    {
        $phpWord = new PhpWord();
        $itemSection = $phpWord->addSection();
        if($contents==null){
            return $this->error('No feeds able to export');
        }
        $itemSection->addText('Title: '.$contents['title'],array('bold' => true));
        $itemSection->addLink('RSS Url: '.$contents['rss_url']);
        $itemSection->addText('Website Description: '.$contents['description']);
        $itemSection->addText(''); // Add a blank line between feeds
        // $sourceMetaData = $titleSection->addText($contents['metadata']);
        
        // dd($contents);
        foreach ($contents->feeds as $item) {
            // $feedContent = $this->feedController->getUserFeed($request,$feed->id);
            $itemSection->addText('Title:'.$item['title'],array('bold' => true));
            $itemSection->addText('Description:',array('bold' => true));
            $itemSection->addText(strip_tags($item['description']));
            $itemSection->addText('Content:',array('bold' => true));
            $itemSection->addText(strip_tags($item['content']));
            $itemSection->addText('Link:',array('bold' => true));
            $itemSection->addLink($item['link']);
            $itemSection->addText('Publication Date:'.$item['pubdate'],array('bold' => true));
            $itemSection->addText(''); // Add a blank line between feeds
        }

        $datetime = now()->format('Y-m-d_H-i-s');

        $filename = 'exported_feeds_'.$datetime.'-'.$contents['title'].'.docx';

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
