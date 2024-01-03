<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LastFetched extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'last_fetched';

    protected $fillable = ['source_id',
                            'last_fetched_feed_id',
                            'last_checked',];
}
