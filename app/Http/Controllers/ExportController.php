<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use App\Http\Controllers\FeedController;
use App\Models\Feed;
use App\Models\Source;
use DOMDocument;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{

    protected $allowedType = ['category','source','tag','feed','folder'];
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
            return $this->exportToWord($userId,$feedsContentFromSource);
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
        $userSource = Source::where('id',$userSourceIds)->first();
        if ($userSource) {
            $feedsContentFromSource = Source::where('id', $userSource->id)
                ->with('feeds')->first();
            $this->exportToWord2($userId,$feedsContentFromSource);
            // $feedsContentFromSources[] = $feedsContentFromSource;
        } else {
            return $this->error('User did not own this source:' . $userSourceIds);
        }
    }

    public function exportFeedsContentFromSource3(Request $request)
    {
        // dd($request);
        if(!isset($request->feed_ids)){
            return $this->error('Need at least one feed to export.');
        }
        $userFeedIds = isset($request->feed_ids)?$request->feed_ids:0;
        $userId = $request->user()->id;
        $feedsContentFromSources = [];
        $feeds = Feed::whereIn('id',$userFeedIds)->get();
        if ($feeds) {
            $file = $this->exportToPdf($userId,$feeds);
            return $this->success(
                $file
            );
        } else {
            return $this->error('Error encounter when exporting');
        }
    }


    public function exportToWord($userId,$contents)
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
        foreach ($contents->feeds as $item) {
            $itemSection->addText('Title:'.$item['title'],array('bold' => true));
            $itemSection->addText('Description:',array('bold' => true));
            $description = preg_replace('/^\s*[\r\n]+/m', '',strip_tags($item['description']));
            $itemSection->addText($description);
            $itemSection->addText('Content:',array('bold' => true));
            libxml_use_internal_errors(true); 
            $doc = new DOMDocument();
            $doc->loadHTML(strip_tags($item['content'],'<body><div><p><h1><h2><h3><h4><h5><h6><p>'));
            \PhpOffice\PhpWord\Shared\Html::addHtml($itemSection,$doc->saveHTML(),true,false);
            $itemSection->addText('Link:',array('bold' => true));
            $itemSection->addLink($item['link']);
            $itemSection->addText('Publication Date:'.$item['pubdate'],array('bold' => true));
            $itemSection->addText(''); // Add a blank line between feeds
            $itemSection->addText('---------------------------------------------------------------------------------------------------------------------------------------'); // Add a blank line between feeds

            // dd($itemSection);
        }

        $datetime = now()->format('Y-m-d_H-i-s');

        $filename = 'exported_feeds_'.$datetime.'-'.str_replace(' ', '_',$contents['title']).'_user_id_'.$userId.'.docx';

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007', $download = true);
        
        header("Content-Disposition: attachment; filename='.$filename.'\''.");
        $objWriter->save($filename);
        $response = $this->fileController->downloadFile($filename);
        return response()->download($response['url']);
    }

    public function exportToWord2($userId,$contents)
    {
        $phpWord = new PhpWord();
        $itemSection = $phpWord->addSection();
        if($contents==null){
            return $this->error('No feeds able to export');
        }
        // dd($contents[0]['title']);
        foreach ($contents as $item) {
            $itemSection->addText('Title:'.$item['title'],array('bold' => true));
            $itemSection->addText('Description:',array('bold' => true));
            $description = preg_replace('/^\s*[\r\n]+/m', '',strip_tags($item['description']));
            $itemSection->addText($description);
            $itemSection->addText('Content:',array('bold' => true));
            libxml_use_internal_errors(true); 
            $doc = new DOMDocument();
            $doc->loadHTML(strip_tags($item['content'],'<body><div><p><h1><h2><h3><h4><h5><h6><p>'));
            \PhpOffice\PhpWord\Shared\Html::addHtml($itemSection,$doc->saveHTML(),true,false);
            
            $itemSection->addText('Link:',array('bold' => true));
            $itemSection->addLink($item['link']);
            $itemSection->addText('Publication Date:'.$item['pubdate'],array('bold' => true));
            $itemSection->addText(''); // Add a blank line between feeds
            $itemSection->addText('---------------------------------------------------------------------------------------------------------------------------------------'); // Add a blank line between feeds
        }

        $datetime = now()->format('Y-m-d_H-i-s');
        $filename = 'exported_feeds_'.$datetime.'_user_id_'.$userId.'.docx';
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007', $download = true);
        
        $objWriter->save($filename);
        $response =  $this->fileController->downloadFile($filename);
        $filePath = $response['url'];
        return ['url'=>$filePath,'filename'=>$filename];
    }

    public function exportToPdf($userId,$contents){

        if($contents==null){
            return $this->error('No feeds able to export');
        }

        $htmlContents = '';

        foreach ($contents as $item) {
            $htmlContents = $htmlContents.'<h1>'.$item['title'].'</h1>
                            <br>
                            <div id=\'article-content\'>'.$item['content'].'</div>'.
                            '<br>';
                            
        }

        $datetime = now()->format('Y-m-d_H-i-s');
        $filename = 'exported_feeds_'.$datetime.'_user_id_'.$userId.'.pdf';
        Pdf::loadHTML($htmlContents)->save($filename);
        $response =  $this->fileController->downloadFile($filename);
        $filePath = $response['url'];
        return ['url'=>$filePath,'filename'=>$filename];
    }

    public function exportToWordByPython($userId,$contents)
    {
        $datetime = now()->format('Y-m-d_H-i-s');
        $filename = 'exported_feeds_'.$datetime.'_user_id_'.$userId.'.docx';

        if($contents==null){
            return $this->error('No feeds able to export');
        }
        $htmlContents = [];
        foreach ($contents as $item) {
            $html = '<p><strong>Title:</strong> ' . htmlspecialchars($item['title']) . '</p>';
            $html .= '<p><strong>Description:</strong> ' . htmlspecialchars($item['description']) . '</p>';
            $html.= $item['content'];
            $html .= '<p><strong>Link:</strong> <a href="' . htmlspecialchars($item['link']) . '">Link</a></p>';
            $html .= '<p><strong>Publication Date:</strong> ' . htmlspecialchars($item['pubdate']) . '</p>';
            $html .= '<br></br>';
            $htmlContents[] = $html;
        }
        $htmlContentsString = implode('', $htmlContents);
        shell_exec("python ". "D:\xampp\htdocsexport_to_words.py " . '<h1>pls work laaahhh</h1>');
    }

    public function testGetFile($localpath){
        return response()->download($localpath);
    }

    public function parseContents($html){
        $html = str_replace(["\n", "\r"], '', $html);
        $html = str_replace(['&lt;', '&gt;', '&amp;', '&quot;'], ['_lt_', '_gt_', '_amp_', '_quot_'], $html);
        $html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
        $html = str_replace('&', '&amp;', $html);
        $html = str_replace(['_lt_', '_gt_', '_amp_', '_quot_'], ['&lt;', '&gt;', '&amp;', '&quot;'], $html);
    }

    public function exportToDocx($contents){
        $phpWord = new PhpWord();
        $itemSection = $phpWord->addSection();
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($contents);
        libxml_use_internal_errors(false); 
        mb_convert_encoding($doc->saveHTML(), 'UTF-8');
        \PhpOffice\PhpWord\Shared\Html::addHtml($itemSection,
                                                $doc->saveHTML(),true,false);
        
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007', $download = true);
        $objWriter->save('export.docx');
        $response =  $this->fileController->downloadFile('export.docx');
        $filePath = $response['url'];
        return $filePath;
    }

    public function getFeedsFromFolder(Request $request,$folderId)
    {
        return $this->userFeedController->getUserFeedsWithFolder($request,$folderId); 
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
