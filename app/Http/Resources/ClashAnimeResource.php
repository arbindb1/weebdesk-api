<?php

namespace App\Http\Resources;

use App\Models\Anime;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Anime */
class ClashAnimeResource extends JsonResource
{
    public function __construct($resource, private string $stat, private bool $revealed = true)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'image_url' => $this->image_url,
            'value' => $this->when($this->revealed, fn () => $this->{$this->stat}),
        ];
    }
}
