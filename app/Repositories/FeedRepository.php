<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Feed;

class FeedRepository extends BaseRepository
{
    /**
     * Create a new repository instance.
     *
     * @param  Feed  $feed
     * @return void
     */
    public function __construct(Feed $feed)
    {
        $this->model = $feed;
    }
}
