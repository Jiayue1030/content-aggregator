<?php

namespace App\Repositories;

use App\Models\Source;

class SourceRepository extends BaseRepository
{
    /**
     * Create a new repository instance.
     *
     * @param  Source  $Source
     * @return void
     */
    public function __construct(Source $source)
    {
        $this->model = $source;
    }
}
