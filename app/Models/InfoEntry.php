<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base;
use App\Models\Source;
use App\Models\UserSource;
use App\Models\Feed;
use App\Models\UserFeed;

class InfoEntry extends Base
{
    use HasFactory;

    protected $allowedInfoType = ['category', 'tag','list','note'];
    protected $allowedOrigin = ['feed','source'];

    protected $fillable = [
        'user_id',
        'type', //['category', 'tag','list','note']
        'type_id',
        'origin', //['feed','source']
        'origin_id',
        'title',
        'description',
        'contents'];

    public function info(){
        return $this->hasOne(Info::class,'id','type_id');
    }

    public function feed(){
        return $this->hasMany(Feed::class,'id','origin_id');
    }   

    public function sources(){
        //1st:final model we wish to access: Source
        //2nd:name of the intermediate model.
        return $this->hasManyThrough(Source::class,
                                    UserSource::class,
                                    'source_id',
                                    'id',
                                    'origin_id',
                                    'source_id'
                                );
    }

    public function feeds(){
        return $this->hasManyThrough(Feed::class,
                                    UserFeed::class,
                                    'feed_id',
                                    'id',
                                    'origin_id',
                                    'feed_id'
                                );
    }

    public function getAllowedInfoType(){
        return $this->allowedInfoType;
    }
    public function getAllowedOrigin(){
        return $this->allowedOrigin;
    }
}
