<?php

namespace App\Jobs;

use App\Http\Controllers\CrawlerController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Feed;
use App\Services\ArticleExtractService;
use Illuminate\Support\Facades\Log;

class ExtractArticleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $crawlerController; 
    protected $url;
    protected $articleExtractService;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Feed $feed,$url){
        $this->url = $url; 
        $this->articleExtractService = new ArticleExtractService();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $url = $this->url;
        //Extract the article contents
        $feedContent = $this->articleExtractService->extractArticle($url);
        // echo('FEEDS:'.$feedContent);
        if($feedContent!='[unable to retrieve full-text content]'){
            echo('Feed id: '.$this->feed->id.'=>success');
            $this->feed->full_content = $feedContent;
        }else{
            echo('Feed id: '.$this->feed->id.'=>omg sadddd!');
        }
        Log::debug("============================
                    Now the feed's content is:".$this->feed->full_content.
                    '============================');
        return $this->feed->save();
    }
}
