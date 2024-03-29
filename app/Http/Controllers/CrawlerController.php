<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use Illuminate\Http\Request;
use Vedmant\FeedReader\Facades\FeedReader;
use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Exception\ClientException;
use \DOMDocument;
use SimplePie;
use Illuminate\Support\Facades\App;
use Graby\Graby;
use Barryvdh\DomPDF\Facade\Pdf;

class CrawlerController extends Controller
{
    protected $fileController;

    public function __construct() {
        $this->fileController = new FileController();
    }
    
    public function getContents($url)
    {
        // $url = $request->url;
        $response = $this->fetchWebsiteContents($url);
        $rssFeedsLinks = null;
        $is_rss = false;

        if ($response->getStatusCode() == 200) {
            $contentType = $response->getHeaders()['Content-Type'][0];
            // echo($contentType);
            if (strpos($contentType, 'text/xml') !== false) {
                $is_rss = true;
                $rssBody = $response->getBody()->getContents();
                return $this->success([
                    'is_rss' => $is_rss,
                    'url'   => $url
                ]);
            } else {
                $is_rss = false;
                $rssFeedsLinks = $this->getRssLinks($url, $response->getBody()->getContents());
                return $this->success([
                    'rss_feeds_link' => $rssFeedsLinks
                ]);
            }
        } else {
            return $this->error('No response from the url.');
        }
    }

    private function fetchWebsiteContents($url)
    {
        $client = new GuzzleClient();

        try {
            $response = $client->get($url);
            // echo('okok');
            // dd($response);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            // $responseBodyAsString = $response->getBody()->getContents();
        }
        // dd($response->getBody());
        return $response;
    }

    /**
     * View the page source to find "application/rss+xml" and extract the rss link
     */
    private function getRssLinks($url, $response)
    {
        $rssFeedsLinks = [];
        $htmlContent = $response;
        $crawler = new Crawler($htmlContent);

        // Look for the RSS feed link in the <head> section
        $feedLinks = $crawler->filter(
            'head link[type="application/rss+xml"]',
        );
        $feedUrls = [];
        // Iterate over the matched elements and extract href attributes
        $feedLinks->each(function (Crawler $link) use (&$feedUrls) {
            $href = $link->attr('href');
            $feedUrls[] = $href;
        });
        $rssFeedsLinks = $feedUrls;

        if (sizeof($rssFeedsLinks) > 0) {
            return $rssFeedsLinks;
        } else {
            return $this->concatUriGetRssLink($url);
        }
    }

    private function concatUriGetRssLink($url)
    {
        // Define the possible URI paths to try
        $uriPaths = ['/feed', '/rss', '/rss.xml'];

        foreach ($uriPaths as $uri) {
            // Concatenate the URI with the base URL
            $url = rtrim($url, '/');
            $newUrl = $url . $uri;
            $response = $this->fetchWebsiteContents($newUrl);
            $contentType = $response->getHeaders()['Content-Type'][0];

            if ($response !== null && strpos($contentType, 'text/xml') !== false) {
                return $newUrl;
            }
        }
        // Return null if none of the URI paths worked
        return null;
    }

    //Return the RSS source information
    public function readRss($rssUrl)
    {
        $feed = new SimplePie\SimplePie($rssUrl, $_SERVER['DOCUMENT_ROOT'] . '/cache');
        $feed->set_feed_url($rssUrl);
        $feed->enable_cache(false);
        // $feed->set_cache_location($_SERVER['DOCUMENT_ROOT'] . '\\app\\cache_files');
        $feed->init();
        $feed->handle_content_type();

        if ($feed->data != null) {
            $rssSourceData = [
                'title' => $feed->get_title(),
                'url'   => $rssUrl, //url source from user
                'rss_url'   => $feed->subscribe_url(), //real rss subscribe url
                'description' => $feed->get_description(),
                'type'  => $feed->get_type(),
                'is_rss' => true,
                'language' => $feed->get_language(),
                'metadata' => [
                    'copyright'  => $feed->get_copyright(),
                    'image_url' => $feed->get_image_url()
                ],
                'author' => $feed->get_authors(),
                'link'  => $feed->get_link(), //Public website from the rss source
            ];
            return response()->json($rssSourceData);
        } else {
            return null; //TODO:This link not support rss (you should call above methods and get that)
        }
    }



    //Return the RSS feeds information
    public function readRssItems($rssUrl)
    {
        $feed = FeedReader::read($rssUrl);
        $feed->force_feed(true);
        $rssItems = $feed->get_items();
        $rssItemsData = [];
        foreach ($rssItems as $rssItem) {
            $itemData = [
                'title' => $rssItem->get_title(),
                'description' => $rssItem->get_description(),
                'content' => $rssItem->get_content(),
                'link' => $rssItem->get_link(),
                'guid' => $rssItem->get_id(),
                'authors' => $rssItem->get_authors(),
                'categories' => $rssItem->get_categories(),
                'pubdate' => $rssItem->get_date('Y-m-d H:i:s'),
            ];

            $rssItemsData[] = $itemData;
        }
        return $rssItemsData;
        //TODO: last check point?
    }



    public function readRssItemsTest(Request $request)
    {
        $this->readRssItems2($request->url);
    }

    //Use SimplePie function to loop through feeds in the rss source
    public function readRssItems2($rssUrl)
    {
        $feed = new SimplePie\SimplePie($rssUrl, $_SERVER['DOCUMENT_ROOT'] . '/cache');
        $feed->set_feed_url($rssUrl);
        // $feed->set_cache_location($_SERVER['DOCUMENT_ROOT'] . '\\app\\cache_files');
        $feed->init();
        $feed->handle_content_type();
        $rssItems = $feed->get_items();
        $rssItemsData = [];
        foreach ($rssItems as $rssItem) {
            // dd($rssItem->get_content());
            $itemData = [
                'title' => $rssItem->get_title(),
                'description' => $rssItem->get_description(),
                // 'content' => $rssItem->get_item_tags($rssItem->get_link(),'content')!=null?
                //                 $rssItem->get_content():
                //                 $this->getContentFromLink($rssItem->get_link()),
                'content' => $rssItem->get_content(),
                'link' => $rssItem->get_link(),
                'guid' => $rssItem->get_id(),
                'authors' => $rssItem->get_authors(),
                'categories' => $rssItem->get_categories(),
                'pubdate' => $rssItem->get_date('Y-m-d H:i:s'),
            ];
            $rssItemsData[] = $itemData;
        }
        return $rssItemsData;
    }

    public function getContentFromLinkTest(Request $request)
    {
        $client = new GuzzleClient();
        $url = urldecode($request->link);
        try {
            $response = $client->get($url);
        } catch (ClientException $e) {
            $response = $e->getResponse();
        }
        $contents = $response->withoutHeader('Transfer-Encoding')->getBody(true)->getContents();

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($contents);
        libxml_use_internal_errors(false);
        $tags = ['head','header', 'footer',
                'nav', 'script', 'meta','style','span','input','form'];
        foreach ($tags as $tag) {
            $nodes = $dom->getElementsByTagName($tag);
            if ($nodes && $nodes->length > 0) {
                for ($i = $nodes->length; --$i >= 0;) {
                    $href = $nodes->item($i);
                    $href->parentNode->removeChild($href);
                }
            }
        }
        $contents = $dom->saveHTML();
        /**This part can work, need to figure out how to donwload pdf */
        $pdf = App::make('dompdf.wrapper')->setOptions(['defaultFont' => 'sans-serif']);
        $pdf->loadHTML($contents);
        return $pdf->stream();
        
    }

    public function getContentFromLink($link)
    {
        $websiteContent = $this->fetchWebsiteContents($link)
            ->getBody()->getContents();
        $htmlContent = $websiteContent;
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($htmlContent);
        libxml_clear_errors();
        $articleContents = "";
        $articleContents = $dom->getElementsByTagName('body');
        // if ($bodyNodeList->length > 0) {
        //     $bodyNode = $bodyNodeList->item(0);
        //     foreach ($bodyNode->childNodes as $node) {
        //     // Exclude specific elements like <header> and <nav>
        //         if (!in_array(strtolower($node->nodeName), ['header', 'nav'])) {
        //             $bodyContents .= $dom->saveHTML($node);
        //         }
        //     }
        // }
        // // Remove elements by tag name
        // $elementsToRemove = ['header', 'nav', 'script'];
        // foreach ($elementsToRemove as $tagName) {
        //     $elements = $dom->getElementsByTagName($tagName);
        //     foreach ($elements as $element) {
        //         $element->parentNode->removeChild($element);
        //     }
        // }

        // // Get contents inside the <article> tag
        // $xpath = new DOMXPath($dom);
        // $articleContents = '';

        // $articleNodeList = $xpath->query('//article/*');
        // if ($articleNodeList->length > 0) {
        //     foreach ($articleNodeList as $node) {
        //         $articleContents .= $dom->saveHTML($node);
        //     }
        // } else {
        //     $articleContents = $dom->saveHTML();
        // }
        // $articleContents = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $articleContents);
        return $articleContents;
    }

    public function php_article_extractor_notwork(Request $request){
        $link = $request->link;
        $graby = new Graby();
        $result = $graby->fetchContent($link);
        $contents = $result;
        $contents = $result->getHtml();
        $contents = '<h1>'.$result->getTitle().'</h1>'.$result->getHtml();
        Pdf::loadHTML($contents)->save('myfile.pdf');
        return $this->fileController->downloadFile('myfile.pdf');
    }

    public function php_article_extractor(Request $request){
        $link = $request->link;
        $graby = new Graby();
        
        $client = new GuzzleClient();
        $url = urldecode($request->link);
        try {
            $response = $client->get($url);
        } catch (ClientException $e) {
            $response = $e->getResponse();
        }
        $contents = $response->withoutHeader('Transfer-Encoding')->getBody(true)->getContents();
        $html = $contents;
        $graby->setContentAsPrefetched($html);
        $result = $graby->fetchContent($link);
        // dd(serialize($result->getHtml()));
        $contents = '<h1>'.$result->getTitle().'</h1>'.$result->getHtml();
        // Pdf::loadHTML($contents)->stream();

        Pdf::loadHTML($contents)->save('myfile.pdf');
        return $this->fileController->downloadFile('myfile.pdf');
    }

    public function getPdfStream(){
        $feedId = 4196;
        $feed = Feed::where('id',$feedId)->first();
        //todo: if the full content is null, call the service once first;
        $feedContents = unserialize($feed->full_content);
        // dd($feedContents);
        Pdf::loadHTML('<h1>'.$feed->title.'</h1>'.$feedContents)->save('getPdfStream.pdf');
    }
}
