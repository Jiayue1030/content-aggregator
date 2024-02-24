<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Attributes as OAT;
use \DOMDocument;
use \DOMXPath;

#[
    OAT\Info(
        version: '1.0.0',
        title: 'Pandora',
        description: "## Introduction\n\n API documentation for Pandora - REST API starter kit powered by Laravel, OpenAPI, Sanctum.\n\n- [GitHub](https://github.com/arifszn/pandora)\n- [MIT License](https://github.com/arifszn/pandora/blob/main/LICENSE)",
    ),
    OAT\Server(url: 'http://localhost', description: 'Local API server'),
    OAT\SecurityScheme(
        securityScheme: 'BearerToken',
        scheme: 'bearer',
        bearerFormat: 'JWT',
        type: 'http'
    ),
    OAT\Tag(name: 'auth', description: 'User authentication'),
    OAT\Tag(name: 'adminAuth', description: 'Admin authentication'),
    OAT\Tag(name: 'profile', description: 'User profile'),
    OAT\Tag(name: 'adminProfile', description: 'Admin profile'),
    OAT\Schema(
        schema: 'ValidationError',
        properties: [
            new OAT\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
            new OAT\Property(
                property: 'errors',
                type: 'object',
                properties: [
                    new OAT\Property(
                        property: 'key 1',
                        type: 'array',
                        items: new OAT\Items(type: 'string', example: 'Error message 1')
                    ),
                    new OAT\Property(
                        property: 'key 2',
                        type: 'array',
                        items: new OAT\Items(type: 'string', example: 'Error message 2')
                    ),
                ]
            ),

        ]
    )
]
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function error($message, $responseCode = '999', $httpCode = 400)
	{
		return $this->respond($httpCode, $responseCode, array('message' => $message));
	}

	protected function success($payload, $responseCode = '000', $httpCode = 200)
	{
		return $this->respond($httpCode, $responseCode, $payload);
	}

	protected function respond($httpCode, $responseCode, $payload = array())
	{
		$response = [
			'code' => $responseCode
		];

		if ( !isset($payload['message']) )
		{
			$payload['message'] = ( $responseCode == '000' ) ? 'success' : 'undefined';
		}

		$response = $response + $payload;
		return $response;
	}

    protected function cleanHtml($html){
        // List of tags to be replaced and their replacement
        $replace_tags = [
            'i' => 'em', 
            'b' => 'strong'
        ];

        // List of tags to be stripped. Text and children tags will be preserved.
        $remove_tags = [
            'acronym', 
            'applet', 
            'b', 
            'basefont', 
            'big', 
            'bgsound', 
            'blink', 
            'center', 
            'del', 
            'dir', 
            'font', 
            'frame', 
            'frameset', 
            'hgroup', 
            'i', 
            'ins', 
            'kbd', 
            'marquee', 
            'nobr', 
            'noframes', 
            'plaintext', 
            'samp', 
            'small', 
            'span', 
            'strike', 
            'tt', 
            'u', 
            'var','header','footer','nav','script','path','meta'
        ];

        // List of attributes to remove. Applied to all tags.
        $remove_attribs = [
            'class', 
            'style', 
            'lang', 
            'width', 
            'height', 
            'align', 
            'hspace', 
            'vspace', 
            'dir'
        ];

        // dd($html);

        $html = $this->replaceTags($html, $replace_tags);
        $html = $this->stripTags($html, $remove_tags);
        $html = $this->stripAttributes($html, $remove_attribs);

        return $html;
    }

    private function replaceTags($html, $tags) {
        // Clean the HTML
        // $html = '<div>' . $html . '</div>'; // Workaround to get the HTML back from DOMDocument without the <html><head> and <body> tags
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_use_internal_errors(true);
        $html = substr($dom->saveHTML($dom->getElementsByTagName('div')->item(0)), 5, -6);
        // Use simple string replace to replace tags
        foreach($tags as $search => $replace) {
            $html = str_replace('<' . $search . '>', '<' . $replace . '>', $html);
            $html = str_replace('<' . $search . ' ', '<' . $replace . ' ', $html);
            $html = str_replace('</' . $search . '>', '</' . $replace . '>', $html);
        }
        return $html;
    }

    private function stripTags($html, $tags) {
        // Remove all attributes from tags to be removed
        // $html = '<div>' . $html . '</div>';
        $dom = new DOMDocument;
        $dom->loadHTML($html);
        foreach($tags as $tag){
            $nodes = $dom->getElementsByTagName($tag);
            foreach($nodes as $node) {
                // Remove attributes
                while($node->attributes->length) {
                    $node->removeAttribute($node->attributes->item(0)->name);
                }
            }
        }
        
        $html = substr($dom->saveHTML($dom->getElementsByTagName('div')->item(0)), 5, -6);
        // Strip tags using string replace
        foreach($tags as $tag){
            $html = str_replace('<' . $tag . '>', '', $html);
            $html = str_replace('</' . $tag . '>', '', $html);
        }
    
        return $html;
    }

    private function stripAttributes($html, $attribs) {
        // Find all nodes that contain the attribute and remove it
        // $html = '<div>' . $html . '</div>';
        $dom = new DOMDocument;
        $dom->loadHTML($html);
        $xPath = new DOMXPath($dom);
        foreach($attribs as $attrib) {
            $nodes = $xPath->query('//*[@' . $attrib . ']');
            foreach($nodes as $node) $node->removeAttribute($attrib);
        }
        return substr($dom->saveHTML($dom->getElementsByTagName('div')->item(0)), 5, -6);
    }
    
}
