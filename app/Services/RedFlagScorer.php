<?php

namespace App\Services;

use App\Models\Character;
use Illuminate\Support\Collection;

class RedFlagScorer
{
    /**
     * Derive red-flag tags from a character biography.
     *
     * @return list<string>
     */
    public function extractTags(?string $about): array
    {
        $about = mb_strtolower((string) $about);
        $tags = [];

        foreach (config('redflags.keywords') as $tag => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($about, $needle)) {
                    $tags[] = $tag;
                    break;
                }
            }
        }

        return $tags;
    }

    /**
     * Score one character by its tags. Unknown tags are ignored.
     *
     * @return array{score: int, flags: array<string, int>, verdict: string}
     */
    public function scoreCharacter(Character $character): array
    {
        $weights = config('redflags.weights');
        $flags = [];

        foreach (array_unique($character->tags ?? []) as $tag) {
            if (isset($weights[$tag])) {
                $flags[$tag] = $weights[$tag];
            }
        }

        $score = min(100, array_sum($flags));

        return ['score' => $score, 'flags' => $flags, 'verdict' => $this->verdict($score)];
    }

    /**
     * Score a set of characters, worst offender first.
     *
     * @param  Collection<int, Character>  $characters
     * @return array{total: int, average: float, results: list<array<string, mixed>>}
     */
    public function scan(Collection $characters): array
    {
        $results = $characters
            ->map(fn (Character $c) => ['character' => $c] + $this->scoreCharacter($c))
            ->sortByDesc('score')
            ->values();

        $total = (int) $results->sum('score');

        return [
            'total' => $total,
            'average' => $results->isEmpty() ? 0.0 : round($total / $results->count(), 1),
            'results' => $results->all(),
        ];
    }

    public function verdict(int $score): string
    {
        $verdicts = config('redflags.verdicts');
        krsort($verdicts);

        foreach ($verdicts as $min => $label) {
            if ($score >= $min) {
                return $label;
            }
        }

        return 'Green flag';
    }
}
