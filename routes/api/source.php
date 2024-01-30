<?php

use App\Http\Controllers\SourceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CrawlerController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\InfoEntryController;
use App\Http\Controllers\UserFeedController;
use App\Jobs\UpdateFeedsJob;
use App\Models\InfoEntry;

/**
 * User Sources
 */
Route::middleware(['auth:user'])->group(function(){

    Route::post('/search',[SourceController::class,'searchUrl']);
    Route::post('/read/rss',[SourceController::class,'readRss']);
    //To get user's added active sources list
    Route::get('/sources',[SourceController::class, 'getUserSourceList']);
    //To view a specific source
    Route::get('/source/get/{id}',[SourceController::class, 'getUserSource']);

    //To add a source to user's sources list
    Route::post('/source/add',[SourceController::class, 'addSource']);
    Route::post('/source/add2',[SourceController::class, 'addSource2']);


    Route::put('/source/update/{usersourceid}',[SourceController::class, 'updateUserSource']);
    Route::post('/source/delete/{usersourceid}',[SourceController::class, 'deleteUserSource']);

    /**
     * FeedController
     */

    //To get the user's feeds list
    Route::get('/feeds',[FeedController::class,'getUserFeedsList']);
    Route::get('/feed/get/{id}',[FeedController::class,'getUserFeed']);
    Route::post('/feed/add/{sourceid}',[FeedController::class,'addFeeds']);
    Route::put('/feed/update/{id}',[FeedController::class,'updateFeed']);
    Route::post('/feed/delete/{id}',[FeedController::class,'deleteFeed']);
    
    Route::post('/read/rss2',[CrawlerController::class,'readRssItemsTest']);

    /**
     * User Contents Management: Folder,Tag,Note,List,Folder
     */
    Route::get('info/tags', [InfoController::class, 'getTagList']);
    Route::get('info/tag/get/{infoTypeId}', [InfoController::class, 'getTagDetail']);
    
    //Get a feed details with sources,categories,tags
    //Example: 'feed/get/1' 'source/get/1'
    Route::get('{origin}/get/{originId}',[InfoEntryController::class,'getOriginDetails']);

    //From an info type(folder,tag), get the list of origins(sources,feeds)
    //Example: feed/folder/get/1 feed/tag/get/1 source/folder/get/1 source/tag/get/1
    // Route::get('{origin}/{infoType}/get/{infoTypeId}',[InfoEntryController::class,'getOriginFromInfoType']);
    
    Route::post('info/tag/add', [InfoController::class, 'addTag']);
    Route::put('info/tag/update/{infoTypeId}', [InfoController::class, 'updateTag']);
    Route::post('info/tag/delete/{infoTypeId}', [InfoController::class, 'deleteTag']);

    Route::get('info/folders', [InfoController::class, 'getFolderList']);
    Route::get('info/folder/get/{infoTypeId}', [InfoController::class, 'getFolderDetail']);
    Route::post('info/folder/add', [InfoController::class, 'addFolder']);
    Route::put('info/folder/update/{infoTypeId}', [InfoController::class, 'updateFolder']);
    Route::post('info/folder/delete/{infoTypeId}', [InfoController::class, 'deleteFolder']);

    Route::post('info/source/folder', [InfoEntryController::class, 'addSourceToFolder']);
    Route::post('info/source/tag/{userSourceId}/{userTagId}', [InfoEntryController::class, 'addSourceToTag']);
    Route::post('info/feed/folder/{userFeedId}/{userTagId}', [InfoEntryController::class, 'addFeedToFolder']);
    Route::post('info/feed/tag/{userFeedId}/{userTagId}', [InfoEntryController::class, 'addFeedToTag']);

    Route::get('info/feed/folder/get/{userFolderId}',[UserFeedController::class,'getUserFeedsWithFolder']);
    
    Route::get('getOriginFromInfoType/{origin}/{infoTypeId}',[InfoEntryController::class,'getOriginFromInfoType']);

    //Example: info/source/folder/1 (Get all sources from folder with id=1)
    Route::get('info/{origin}/{infoType}/{infoId}',[
        InfoController::class,
        'getInfoEntryFromInfoType']); 
    
    //Example: info/feed/get/1
    Route::get('info/{originType}/get/{originId}',[InfoEntryController::class,'getInfoTypesFromOrigin']);
    
    // Route::get('/export/feeds/{userSourceId}', [ExportController::class,'exportFeedsContentFromSource']);
    Route::POST('/export/feeds', [ExportController::class,'exportFeedsContentFromSource3']);
    
    Route::get('/get/feeds/with_folder',[UserFeedController::class,'getUserFeedsWithFolder']);

    
    /**
     * Export
     */

     Route::get('test/latest',[UpdateFeedsJob::class,'getLatestFeedIdFromSource']);

     Route::get('test/jobs',[UpdateFeedsJob::class,'handle']);

     Route::post('test/getContentFromLink',[CrawlerController::class,'getContentFromLinkTest']);
});