<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base;

class Feed extends Base
{
    // use HasFactory,SoftDeletes,HasApiTokens;

    protected $casts = ['categories'=>'array',
                        'authors'=>'array',
                        'created_at' => 'datetime:Y-m-d h:m:s',
                        'updated_at' => 'datetime:Y-m-d h:m:s'];

    // protected $dates = ['pubdate','created_at','updated_at'];
    
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

    public function source(){
        return $this->hasOne(Source::class,'id','source_id');
    }
}
