<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Source;
use App\Models\Info;

class UserSource extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = ['user_id','source_id','name','reference','status','created_by'];

    public function source(){
        return $this->hasMany(Source::class,'id','source_id');
    }

    public function feeds(){
        return $this->hasMany(Feed::class,'id','feed_id');
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
