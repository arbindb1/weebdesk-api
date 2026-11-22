<?php

namespace App\Models;

use Database\Factories\AnimeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Anime extends Model
{
    /** @use HasFactory<AnimeFactory> */
    use HasFactory;

    protected $table = 'anime';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'score' => 'float',
            'is_seasonal' => 'boolean',
            'is_top' => 'boolean',
        ];
    }

    public function characters(): HasMany
    {
        return $this->hasMany(Character::class);
    }
}
