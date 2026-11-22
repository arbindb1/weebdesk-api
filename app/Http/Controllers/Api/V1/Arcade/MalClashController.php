<?php

namespace App\Http\Controllers\Api\V1\Arcade;

use App\Http\Controllers\Controller;
use App\Http\Requests\Arcade\MalClashBatchRequest;
use App\Http\Requests\Arcade\MalClashGuessRequest;
use App\Http\Resources\ClashResultResource;
use App\Http\Resources\ClashRoundResource;
use App\Models\Anime;
use App\Services\MalClashService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MalClashController extends Controller
{
    public function __construct(private MalClashService $clash) {}

    public function batch(MalClashBatchRequest $request): AnonymousResourceCollection
    {
        $rounds = $this->clash->batch(
            $request->validated('stat') ?? 'score',
            (int) ($request->validated('rounds') ?? 10),
        );

        return ClashRoundResource::collection($rounds);
    }

    public function guess(MalClashGuessRequest $request): ClashResultResource
    {
        $data = $request->validated();

        return new ClashResultResource($this->clash->check(
            $data['stat'],
            Anime::findOrFail($data['left_id']),
            Anime::findOrFail($data['right_id']),
            $data['guess'],
        ));
    }
}
