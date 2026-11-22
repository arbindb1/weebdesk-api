<?php

namespace Database\Factories;

use App\Models\Anime;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Anime> */
class AnimeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'mal_id' => fake()->unique()->numberBetween(1, 900000),
            'title' => fake()->unique()->words(3, true),
            'image_url' => fake()->imageUrl(),
            'type' => 'TV',
            'score' => fake()->randomFloat(2, 5, 9.5),
            'popularity' => fake()->numberBetween(1, 20000),
            'members' => fake()->numberBetween(1000, 3000000),
            'favorites' => fake()->numberBetween(0, 200000),
        ];
    }
}
