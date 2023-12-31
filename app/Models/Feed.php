<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base;

class Feed extends Base
{
    // use HasFactory,SoftDeletes,HasApiTokens;

    protected $casts = ['categories'=>'array',
                        'authors'=>'array'];
    
    protected $fillable = [
        'title',
        'description',
        'content',
        'link',
        'guid',
        'categories',
        'pubdate',
        'authors',
        'source_id'
        // 'contents'
    ];
}
