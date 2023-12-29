<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\UserSource;

class UserSourcesResource extends JsonResource
{
    public function __construct(private UserSource $userSource)
    {
        parent::__construct($userSource);
    }
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */

    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
