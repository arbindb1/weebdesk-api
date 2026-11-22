<?php

namespace App\Http\Controllers\Api\V1\Arcade;

use App\Http\Controllers\Controller;
use App\Http\Requests\Arcade\RedFlagScanRequest;
use App\Http\Resources\CharacterResource;
use App\Http\Resources\RedFlagScanResource;
use App\Models\Character;
use App\Services\RedFlagScorer;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RedFlagController extends Controller
{
    public function __construct(private RedFlagScorer $scorer) {}

    /** Random characters whose tags have been synced. */
    public function characters(): AnonymousResourceCollection
    {
        $characters = Character::query()
            ->with('anime:id,title')
            ->whereNotNull('tags')
            ->inRandomOrder()
            ->limit((int) config('redflags.characters_per_scan'))
            ->get();

        return CharacterResource::collection($characters);
    }

    public function scan(RedFlagScanRequest $request): RedFlagScanResource
    {
        $characters = Character::with('anime:id,title')
            ->whereIn('id', $request->validated('character_ids'))
            ->get();

        return new RedFlagScanResource($this->scorer->scan($characters));
    }
}
