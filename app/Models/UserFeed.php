<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Base;
use App\Models\InfoEntry;

class UserFeed extends Base
{
    use HasFactory,SoftDeletes,HasApiTokens;

    protected $fillable = [
        'user_id','source_id','feed_id','references','status','is_read','is_star'
    ];

    public function feed(){
        return $this->hasOne(Feed::class,'id','feed_id');
    }

    public function categories(){
        return $this->hasMany(InfoEntry::class,'origin_id','source_id')
                ->with('info')
                ->where('origin','source')->where('type','category');
    }

    public function tags(){
        return $this->hasMany(InfoEntry::class,'origin_id','source_id')
                ->with('info')
                ->where('origin','source')->where('type','tag');
    }

}
