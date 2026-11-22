<?php

namespace Database\Factories;

use App\Models\Anime;
use App\Models\Character;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Character> */
class CharacterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'mal_id' => fake()->unique()->numberBetween(1, 900000),
            'anime_id' => Anime::factory(),
            'name' => fake()->name(),
            'image_url' => fake()->imageUrl(),
            'role' => 'Main',
            'favorites' => fake()->numberBetween(0, 50000),
            'tags' => [],
        ];
    }
}
