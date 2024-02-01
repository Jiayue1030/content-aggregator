<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Vedmant\FeedReader\Facades\FeedReader;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\JsonResponse;
use Symfony\Component\DomCrawler\Crawler;
use Weidner\Goutte\GoutteFacade as Goutte;
use Illuminate\Support\Facades\Response;
use GuzzleHttp\Exception\ClientException;
use SimplePie\Author;
use \DOMDocument;
use DOMXPath;
use SimplePie;

class CrawlerController extends Controller
{
    public function getContents($url) {
        // $url = $request->url;
        $response = $this->fetchWebsiteContents($url);
        $rssFeedsLinks = null;
        $is_rss = false;

        if($response->getStatusCode() == 200) {
            $contentType = $response->getHeaders()['Content-Type'][0];
            // echo($contentType);
            if(strpos($contentType,'text/xml')!==false){
                $is_rss = true;
                $rssBody = $response->getBody()->getContents();
                return $this->success([
                    'is_rss'=> $is_rss,
                    'url'   => $url
                ]);
            }else{
                $is_rss = false;
                $rssFeedsLinks = $this->getRssLinks($url,$response->getBody()->getContents());
                return $this->success([
                    'rss_feeds_link' => $rssFeedsLinks
                ]);
            }
        }else{
            return $this->error('No response from the url.');
        }
    }

    private function fetchWebsiteContents($url){
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
    private function getRssLinks($url,$response){
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

        if(sizeof($rssFeedsLinks)>0){
            return $rssFeedsLinks;
        }else{
            return $this->concatUriGetRssLink($url);
        }
    }

    private function concatUriGetRssLink($url){
        // Define the possible URI paths to try
        $uriPaths = ['/feed', '/rss', '/rss.xml'];

        foreach ($uriPaths as $uri) {
            // Concatenate the URI with the base URL
            $url = rtrim($url,'/');
            $newUrl = $url.$uri;
            $response = $this->fetchWebsiteContents($newUrl);
            $contentType = $response->getHeaders()['Content-Type'][0];

            if ($response !== null && strpos($contentType,'text/xml')!==false) {
                return $newUrl;
            }
        }
        // Return null if none of the URI paths worked
        return null;
    }

    //Return the RSS source information
    public function readRss($rssUrl){
        $feed = new SimplePie\SimplePie($rssUrl,$_SERVER['DOCUMENT_ROOT'].'/cache');
        $feed->set_feed_url($rssUrl);
        $feed->enable_cache(false);
        // $feed->set_cache_location($_SERVER['DOCUMENT_ROOT'] . '\\app\\cache_files');
        $feed->init();
        $feed->handle_content_type();
        
        if($feed->data!=null){
            $rssSourceData = [
                'title' => $feed->get_title(),
                'url'   => $rssUrl, //url source from user
                'rss_url'   => $feed->subscribe_url(), //real rss subscribe url
                'description' => $feed->get_description(),
                'type'  => $feed->get_type(),
                'is_rss' => true,
                'language' => $feed->get_language(),
                'metadata' => ['copyright'  => $feed->get_copyright(),
                                'image_url' => $feed->get_image_url()],
                'author' => $feed->get_authors(),
                'link'  => $feed->get_link(), //Public website from the rss source
            ];
            return response()->json($rssSourceData);
        }else{
            return null; //TODO:This link not support rss (you should call above methods and get that)
        }
    }

    

    //Return the RSS feeds information
    public function readRssItems($rssUrl){
        $feed = FeedReader::read($rssUrl);
        $feed->force_feed(true);
        // dd($f); //https://www.mdpi.com/journal/sustainability
        $rssItems = $feed->get_items();
        $rssItemsData = [];
        foreach ($rssItems as $rssItem) {
            $itemData = [
                'title' => $rssItem->get_title(),
                'description' => $rssItem->get_description(),
                // 'content' => $rssItem->get_item_tags($rssItem->get_link(),'content')!=null?
                //                 $rssItem->get_content():
                //                 $this->getContentFromLink($rssItem->get_link()),
                'content'=>$rssItem->get_content(),
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

    

    public function readRssItemsTest(Request $request){
        $this->readRssItems2($request->url);
    }

    //Use SimplePie function to loop through feeds in the rss source
    public function readRssItems2($rssUrl){
        $feed = new SimplePie\SimplePie($rssUrl,$_SERVER['DOCUMENT_ROOT'].'/cache');
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
                'content'=>$rssItem->get_content(),
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

    public function getContentFromLinkTest(Request $request){
        return $this->getContentFromLink(urldecode($request->link));
    }

    public function getContentFromLink($link){
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
}
