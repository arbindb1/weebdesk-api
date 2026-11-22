<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Wraps ['stat' => string, 'left' => Anime, 'right' => Anime]. */
class ClashRoundResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $stat = $this->resource['stat'];

        return [
            'stat' => $stat,
            'left' => new ClashAnimeResource($this->resource['left'], $stat),
            'right' => new ClashAnimeResource($this->resource['right'], $stat, revealed: false),
        ];
    }
}
