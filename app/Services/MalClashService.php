<?php

namespace App\Services;

use App\Models\Anime;

class MalClashService
{
    public const STATS = ['score', 'members', 'favorites'];

    /**
     * Build Higher/Lower rounds. The left anime reveals its stat; the right one is hidden.
     *
     * @return list<array{stat: string, left: Anime, right: Anime}>
     */
    public function batch(string $stat, int $rounds): array
    {
        $anime = Anime::query()
            ->whereNotNull($stat)
            ->inRandomOrder()
            ->limit($rounds * 2)
            ->get(['id', 'title', 'image_url', $stat])
            ->values();

        $pairs = [];
        for ($i = 0; $i + 1 < $anime->count(); $i += 2) {
            $pairs[] = ['stat' => $stat, 'left' => $anime[$i], 'right' => $anime[$i + 1]];
        }

        return $pairs;
    }

    /**
     * Ties count as correct for either guess.
     *
     * @return array{correct: bool, stat: string, left: Anime, right: Anime}
     */
    public function check(string $stat, Anime $left, Anime $right, string $guess): array
    {
        $l = $left->{$stat};
        $r = $right->{$stat};

        $correct = $l == $r || ($guess === 'higher' ? $r > $l : $r < $l);

        return ['correct' => $correct, 'stat' => $stat, 'left' => $left, 'right' => $right];
    }
}
