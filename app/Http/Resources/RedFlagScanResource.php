<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Wraps the result array from RedFlagScorer::scan(). */
class RedFlagScanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'total' => $this->resource['total'],
            'average' => $this->resource['average'],
            'results' => array_map(fn (array $r) => [
                'character' => new CharacterResource($r['character']),
                'score' => $r['score'],
                'verdict' => $r['verdict'],
                'flags' => $r['flags'],
            ], $this->resource['results']),
        ];
    }
}
