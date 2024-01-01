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
        } catch (ClientException $e) {
            $response = $e->getResponse();
            // $responseBodyAsString = $response->getBody()->getContents();
        }
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
        // $rssUrl = $request->url;
        $f = FeedReader::read($rssUrl);
        //parse these data into what SouceModel needed
        // dd($f);
        $rssSourceData = [
            'title' => $f->get_title(),
            'url'   => $f->subscribe_url(),
            'description' => $f->get_description(),
            'type'  => $f->get_type(),
            'is_rss' => true,
            'language' => $f->get_language(),
            'metadata' => ['copyright'  => $f->get_copyright(),
                            'image_url' => $f->get_image_url()],
            'author' => $f->get_authors(),
            'link'  => $f->get_link(), //Public website
        ];
        return response()->json($rssSourceData);
    }

    //Return the RSS feeds information
    public function readRssItems($rssUrl){
        // $rssUrl = $request->url;
        // $rssUrl = $request->url;
        $f = FeedReader::read($rssUrl);
        // dd($f->get_item(2)->get_categories());
        $rssItems = $f->get_items();
        $rssItemsData = [];

        foreach ($rssItems as $rssItem) {
            $rssItemsData[] = [
                'title' => $rssItem->get_title(),
                'description' => $rssItem->get_description(),
                'content' => $rssItem->get_content(),
                'link' => $rssItem->get_link(),
                'guid' => $rssItem->get_id(),
                'authors' => $rssItem->get_authors(),
                'categories' => $rssItem->get_categories(),
                'pubdate' => $rssItem->get_date(),
                // 'contents' => $rssItem->get_content(),
            ];
        }
        return $rssItemsData;
        //TODO: last check point?
    }

}
