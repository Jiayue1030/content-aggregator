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
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Storage;

class ExportController extends Controller
{

    protected $allowedType = ['category','source','tag','feed'];
    protected $feedController = null;
    protected $userFeedController = null;
    protected $fileController = null;

    public function __construct() {
        $this->feedController = new FeedController();
        $this->userFeedController = new UserFeedController();
        $this->fileController = new FileController();
    }

    public function exportFeedsContentFromSource(Request $request,$userSourceId)
    {
        $userId = $request->user()->id;
        $userSource = Source::where('id',$userSourceId)->first();

        // $userSource = UserSource::where('id',$userSourceId)
        //               ->where('user_id',$userId)
        //               ->get()->first();
        $feedsContentFromSource = null;

        if($userSource){
            $feedsContentFromSource = Source::where('id',$userSource->id)
                        ->with('feeds')->get()->first();
            // dd($feedsContentFromSource);
            return $this->exportToWord($feedsContentFromSource);
        }else{
            return $this->error('User did not own this source:'.$userSourceId);
        }
        // return $feedsContentFromSource;
    }

    public function exportFeedsContentFromSource2(Request $request)
    {
        if(!isset($request->user_source_ids)){
            return $this->error('Need at least one source to export feeds!');
        }
        $userSourceIds = isset($request->user_source_ids)?$request->user_source_ids:0;

        $userId = $request->user()->id;
        $feedsContentFromSources = [];
        // dd();
        foreach ($userSourceIds as $userSourceId) {
            $userSource = Source::where('id',$userSourceId)->first();
            if ($userSource) {
                $feedsContentFromSource = Source::where('id', $userSource->id)
                    ->with('feeds')->first();
                return $this->exportToWord($feedsContentFromSource);
                $feedsContentFromSources[] = $feedsContentFromSource;
            } else {
                return $this->error('User did not own this source:' . $userSourceId);
            }
        }
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
            // dd(strip_tags($item['description']));
            $description = preg_replace('/^\s*[\r\n]+/m', '',strip_tags($item['description']));
            // dd($description);
            $itemSection->addText($description);
            $itemSection->addText('Content:',array('bold' => true));
            libxml_use_internal_errors(true); 
            $doc = new DOMDocument();
            $doc->loadHTML(strip_tags($item['content'],'<body><div><p><h1><h2><h3><h4><h5><h6><p>'));
            // dd($doc->saveHTML());
            \PhpOffice\PhpWord\Shared\Html::addHtml($itemSection,$doc->saveHTML(),true,false);
            
            // $itemSection->addText(strip_tags($item['content']));
            $itemSection->addText('Link:',array('bold' => true));
            $itemSection->addLink($item['link']);
            $itemSection->addText('Publication Date:'.$item['pubdate'],array('bold' => true));
            $itemSection->addText(''); // Add a blank line between feeds
            $itemSection->addText('---------------------------------------------------------------------------------------------------------------------------------------'); // Add a blank line between feeds

            // dd($itemSection);
        }

        $datetime = now()->format('Y-m-d_H-i-s');

        $filename = 'exported_feeds_'.$datetime.'-'.$contents['title'].'.docx';

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007', $download = true);
        
        header("Content-Disposition: attachment; filename='.$filename.'\''.");
        $objWriter->save($filename);
        // return $this->fileController->downloadFile($filename);
        return response()->download($filename);
    }

    public function parseContents($html){
        $html = str_replace(["\n", "\r"], '', $html);
        $html = str_replace(['&lt;', '&gt;', '&amp;', '&quot;'], ['_lt_', '_gt_', '_amp_', '_quot_'], $html);
        $html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
        $html = str_replace('&', '&amp;', $html);
        $html = str_replace(['_lt_', '_gt_', '_amp_', '_quot_'], ['&lt;', '&gt;', '&amp;', '&quot;'], $html);
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
