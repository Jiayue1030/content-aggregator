<?php

namespace App\Services;

use Graby\Graby;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;

class ArticleExtractService
{
    public function extractArticle($url){
        $graby = new Graby();
        
        $client = new GuzzleClient();
        $url = urldecode($url);
        try {
            $response = $client->get($url);
        } catch (ClientException $e) {
            $response = $e->getResponse();
        }
        $contents = $response->withoutHeader('Transfer-Encoding')->getBody(true)->getContents();
        $graby->setContentAsPrefetched($contents);
        $result = $graby->fetchContent($url);
        $articleContent = '';
        $serializedArticleContent = serialize($result->getHtml()); //Convert graby object to serialzied tring
        $articleContent = unserialize($serializedArticleContent); //Convert serialized string into string
        return $articleContent;
    }
}