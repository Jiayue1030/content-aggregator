<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SourceController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'profile'], function () {
    Route::get('/', [ProfileController::class, 'me']);
    Route::get('/sources',[SourceController::class, 'getUserSources']);
});

// Route::middleware(['auth:user'])->group(['prefix' => 'users'],function(){
    
//     //To get user's added active sources list
//     Route::get('/sources',[SourceController::class, 'getUserSources']);

//     //To add a source to user's sources list
//     Route::post('/add/source',[SourceController::class, 'getUserSources']);

//     //To view a specific source
//     Route::get('/sources',[SourceController::class, 'getUserSources']);
// });
