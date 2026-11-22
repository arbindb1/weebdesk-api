<?php

use App\Models\Anime;
use App\Models\Character;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function jikanAnime(int $id, array $overrides = []): array
{
    return $overrides + [
        'mal_id' => $id,
        'title' => "Anime $id",
        'title_english' => null,
        'images' => ['jpg' => ['large_image_url' => "https://img/$id.jpg"]],
        'type' => 'TV',
        'episodes' => 12,
        'score' => 8.5,
        'rank' => $id,
        'popularity' => $id,
        'members' => 1000 * $id,
        'favorites' => 10 * $id,
        'season' => 'fall',
        'year' => 2026,
    ];
}

function fakeJikan(): void
{
    Http::fake([
        '*/seasons/2026/fall*' => Http::response([
            'data' => [jikanAnime(1), jikanAnime(2)],
            'pagination' => ['has_next_page' => false],
        ]),
        '*/top/anime*' => Http::response([
            'data' => [jikanAnime(2), jikanAnime(3)],
            'pagination' => ['has_next_page' => false],
        ]),
        '*/anime/*/characters' => Http::response([
            'data' => [
                ['character' => ['mal_id' => 100, 'name' => 'Yuno', 'images' => ['jpg' => ['image_url' => 'x']]], 'role' => 'Main', 'favorites' => 500],
            ],
        ]),
        '*/characters/100' => Http::response(['data' => ['about' => 'A yandere who will stalk and murder anyone.']]),
    ]);
}

it('syncs seasonal and top anime without duplicating overlaps', function () {
    fakeJikan();

    $this->artisan('anime:sync', ['--delay' => 0, '--characters' => 0, '--top' => 2])->assertSuccessful();

    expect(Anime::count())->toBe(3);
    expect(Anime::where('mal_id', 1)->first()->is_seasonal)->toBeTrue();
    expect(Anime::where('mal_id', 1)->first()->is_top)->toBeFalse();

    $overlap = Anime::where('mal_id', 2)->first();
    expect($overlap->is_seasonal)->toBeTrue()->and($overlap->is_top)->toBeTrue();
});

it('syncs characters and derives red-flag tags', function () {
    fakeJikan();

    $this->artisan('anime:sync', ['--delay' => 0, '--characters' => 1, '--top' => 2])->assertSuccessful();

    $character = Character::where('mal_id', 100)->first();
    expect($character)->not->toBeNull()
        ->and($character->tags)->toContain('yandere', 'stalker', 'violent');
});

it('is idempotent across repeated runs', function () {
    fakeJikan();

    $this->artisan('anime:sync', ['--delay' => 0, '--characters' => 1, '--top' => 2])->assertSuccessful();
    $this->artisan('anime:sync', ['--delay' => 0, '--characters' => 1, '--top' => 2])->assertSuccessful();

    expect(Anime::count())->toBe(3)->and(Character::count())->toBe(1);
});

it('respects the top limit', function () {
    Http::fake([
        '*/seasons/*' => Http::response(['data' => [], 'pagination' => ['has_next_page' => false]]),
        '*/top/anime*' => Http::response([
            'data' => [jikanAnime(1), jikanAnime(2), jikanAnime(3)],
            'pagination' => ['has_next_page' => true],
        ]),
    ]);

    $this->artisan('anime:sync', ['--delay' => 0, '--characters' => 0, '--top' => 2])->assertSuccessful();

    expect(Anime::count())->toBe(2);
});
