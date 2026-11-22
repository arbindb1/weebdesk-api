<?php

use App\Models\Anime;
use App\Models\Character;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function guest(): array
{
    return ['X-Guest-UUID' => (string) Str::uuid()];
}

it('rejects arcade requests without a valid guest uuid', function () {
    $this->getJson('/api/v1/arcade/mal-clash')->assertStatus(400);
    $this->getJson('/api/v1/arcade/mal-clash', ['X-Guest-UUID' => 'nope'])->assertStatus(400);
});

it('serves a mal clash batch hiding the right-hand value', function () {
    Anime::factory()->count(6)->create();

    $response = $this->getJson('/api/v1/arcade/mal-clash?stat=members&rounds=3', guest())
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure(['data' => [['stat', 'left' => ['id', 'title', 'image_url', 'value'], 'right' => ['id', 'title', 'image_url']]]]);

    expect($response->json('data.0.right'))->not->toHaveKey('value');
});

it('validates mal clash params', function () {
    $this->getJson('/api/v1/arcade/mal-clash?stat=bogus', guest())
        ->assertUnprocessable()->assertJsonValidationErrors('stat');
});

it('judges higher/lower guesses', function (string $guess, bool $expected) {
    $left = Anime::factory()->create(['score' => 7.0]);
    $right = Anime::factory()->create(['score' => 8.0]);

    $this->postJson('/api/v1/arcade/mal-clash/guess', [
        'stat' => 'score', 'left_id' => $left->id, 'right_id' => $right->id, 'guess' => $guess,
    ], guest())
        ->assertOk()
        ->assertJson(['data' => ['correct' => $expected, 'left_value' => 7, 'right_value' => 8]]);
})->with([['higher', true], ['lower', false]]);

it('counts ties as correct', function () {
    $left = Anime::factory()->create(['score' => 8.0]);
    $right = Anime::factory()->create(['score' => 8.0]);

    $this->postJson('/api/v1/arcade/mal-clash/guess', [
        'stat' => 'score', 'left_id' => $left->id, 'right_id' => $right->id, 'guess' => 'lower',
    ], guest())->assertJson(['data' => ['correct' => true]]);
});

it('returns random characters for the red flag scanner', function () {
    Character::factory()->count(7)->create();
    Character::factory()->create(['tags' => null]); // unsynced, must be excluded

    $this->getJson('/api/v1/arcade/red-flag/characters', guest())
        ->assertOk()
        ->assertJsonCount(5, 'data')
        ->assertJsonStructure(['data' => [['id', 'name', 'image_url', 'anime']]]);
});

it('scores five characters by tag weights, worst first', function () {
    $tagSets = [
        ['yandere', 'stalker', 'violent'], // 40+30+25 = 95
        [],                                  // 0
        ['jealous'],                         // 10
        ['unknown_tag'],                     // ignored -> 0
        ['yandere', 'yandere', 'sadistic'],  // duplicates collapse: 40+35 = 75
    ];
    $ids = collect($tagSets)->map(fn ($tags) => Character::factory()->create(['tags' => $tags])->id)->all();

    $response = $this->postJson('/api/v1/arcade/red-flag/scan', ['character_ids' => $ids], guest())
        ->assertOk();

    expect($response->json('data.results.*.score'))->toBe([95, 75, 10, 0, 0])
        ->and($response->json('data.results.0.verdict'))->toBe('Run')
        ->and($response->json('data.total'))->toBe(180)
        ->and($response->json('data.average'))->toBe(36);
});

it('requires exactly five distinct existing characters', function () {
    $ids = Character::factory()->count(3)->create()->pluck('id')->all();

    $this->postJson('/api/v1/arcade/red-flag/scan', ['character_ids' => $ids], guest())
        ->assertUnprocessable()->assertJsonValidationErrors('character_ids');

    $this->postJson('/api/v1/arcade/red-flag/scan', ['character_ids' => [1, 1, 1, 1, 1]], guest())
        ->assertUnprocessable();
});
