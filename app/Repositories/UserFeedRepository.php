<?php

namespace App\Repositories;

use App\Models\Feed;
use App\Models\UserFeed;

class UserFeedRepository extends BaseRepository
{
    public function __construct(UserFeed $userFeed)
    {
        $this->model = $userFeed;
    }

    public function getUserFeeds($condition=[],int $page=20)
    {
        $userFeeds = UserFeed::with('feed')
                        ->where($condition)
                        ->get()
                        ->paginate($page);
        return $userFeeds;
    }
}