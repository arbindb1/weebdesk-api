<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Wraps the result array from MalClashService::check(). */
class ClashResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $stat = $this->resource['stat'];

        return [
            'correct' => $this->resource['correct'],
            'stat' => $stat,
            'left_value' => $this->resource['left']->{$stat},
            'right_value' => $this->resource['right']->{$stat},
        ];
    }
}
