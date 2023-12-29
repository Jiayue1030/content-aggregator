<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use App\Models\Source;
use OpenApi\Attributes as OAT;

class SourceResource extends JsonResource
{

    public function __construct(private Source $source)
    {
        parent::__construct($source);
    }

    public function toArray($request): array|Arrayable|JsonSerializable
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'url' => $this->url,
            'description' => $this->description,
            'type' => $this->type,
            'is_rss' => $this->is_rss,
            'language'=> $this->language,
            'metadata'=>$this->metadata,
            'created_by' => $this->created_by,
            'author'=>$this->author
        ];
    }
}
