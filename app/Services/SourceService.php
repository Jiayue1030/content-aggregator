<?php

namespace App\Services;

use App\Models\Source;
use App\Models\User;
use App\Models\UserSource;
use App\Repositories\SourceRepository;

class SourceService
{
    /**
     * Create a new service instance.
     *
     * @param  SourceRepository  $sourceRepository
     * @return void
     */
    public function __construct(private SourceRepository $sourceRepository)
    {
        //
    }

    /**
     * Store a new user.
     *
     * @param  array  $data
     * @return Source
     */
    public function storeSource(array $data): Source
    {
        return $this->sourceRepository->create($data);
    }

    /**
     * Get a user by email.
     *
     * @param  int  $user_id
     * @return null|Source
     */
    public function getUserSources(int $user_id): ?Source
    {
        return $this->sourceRepository->get(['user_id' => $user_id],$takeOne=false);
    }

    public function checkUrlExistence(string $url)
    {
        return Source::where('url', $url)->first();
    }

    public function userHasSource(int $user_id, int $source_id): bool
    {
        return UserSource::where('user_id', $user_id)
            ->where('source_id', $source_id)
            ->exists();
    }
}
