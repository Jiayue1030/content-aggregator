<?php

namespace App\Services;

use App\Models\Feed;
use App\Models\User;
use App\Models\UserFeed;
use App\Repositories\FeedRepository;
use App\Repositories\UserFeedRepository;

class FeedService
{
    /**
     * Create a new service instance.
     *
     * @param  FeedRepository  $feedRepository
     * @return void
     */
    public function __construct(private FeedRepository $feedRepository,
                                private UserFeedRepository $userFeedRepository)
    {   
        
    }

    public function getUserFeeds(array $condition=[],int $page=20)
    {
        return $this->userFeedRepository->getUserFeeds([$condition],$page);
    }

    public function addFeed(array $data): Feed
    {
        return $this->feedRepository->create($data);
    }

    public function addUserFeed(array $data): UserFeed
    {
        return $this->userFeedRepository->create($data);
    }

    public function updateFeed(Feed $feed,$data): bool
    { 
        $feed = $this->feedRepository->get();
        return $this->feedRepository->update($feed,$data);
    }

    public function deleteFeed(Feed $feed)
    {
        return $this->feedRepository->delete($feed);
    }
    

    public function checkGuidExistence(string $guid)
    {
        return $this->feedRepository->get(['guid' => $guid],$takeOne=false);
    }

    public function userHasFeed(int $user_id, int $feed_id): bool
    {
        return UserFeed::where('user_id', $user_id)
            ->where('feed_id', $feed_id)
            ->exists();
    }
}
