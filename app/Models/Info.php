<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use App\Models\InfoEntry;
use App\Models\UserSource;
use Illuminate\Support\Facades\DB;

class Info extends Model
{
    use HasApiTokens,SoftDeletes,HasFactory;

    protected $allowedInfoType = ['folder', 'tag','category'];

    protected $fillable = ['user_id',
                            'type',
                            'title',
                            'description',
                            'references',];

    public function getAllowedInfoType(){
        return $this->allowedInfoType;
    }

    public function user(){
        return $this->hasOne(User::class,'id','user_id');
    }

    public function source(){
        // return $this->hasMany(InfoEntry::class,'type_id','id');
        return $this->hasManyThrough(Source::class,InfoEntry::class,'type_id','id','id','origin_id')
        ->where('origin','source');
    }

    public function feed(){
        return $this->hasMany(InfoEntry::class,'type_id','id')
        ->where('origin','feed');
    }



    
}
