<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use App\Models\UserSource;
 
class Source extends Model
{
    use HasFactory,SoftDeletes,HasApiTokens;

    protected $casts = ['metadata' => 'array','author' => 'array'];

    protected $hidden = ['created_by'];

    protected $fillable = [
        'id',
        'title',
        'url', //url source from user
        'rss_url',//real rss subscribe url
        'link',//Public website from the rss source
        'description',
        'type',
        'is_rss',
        'language',
        'metadata',
        'author',
        'created_by'
    ];

    public function userSources(){
        return $this->hasMany(UserSource::class,'source_id','id');
    }

    public function feeds(){
        return $this->hasMany(Feed::class,'source_id','id');
    }

    public function user_feeds(){
        return $this->hasMany(UserFeed::class,'source_id','id');
    }

}
