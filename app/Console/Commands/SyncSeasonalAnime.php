<?php

namespace App\Console\Commands;

use App\Models\Anime;
use App\Models\Character;
use App\Services\RedFlagScorer;
use Illuminate\Console\Command;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class SyncSeasonalAnime extends Command
{
    protected $signature = 'anime:sync
        {--year=2026 : Season year}
        {--season=fall : winter|spring|summer|fall}
        {--top=200 : How many top-ranked anime to sync}
        {--characters=5 : Characters to sync per anime (0 to skip)}
        {--delay=400 : Milliseconds to wait between Jikan requests}';

    protected $description = 'Sync a Jikan v4 season plus the top anime and their characters into the database';

    public function handle(RedFlagScorer $scorer): int
    {
        $this->info('Syncing season...');
        $seasonal = $this->syncSeason();

        $this->info('Syncing top anime...');
        $top = $this->syncTop((int) $this->option('top'));

        $perAnime = (int) $this->option('characters');
        if ($perAnime > 0) {
            $this->info('Syncing characters...');
            $ids = $seasonal->merge($top)->unique();
            $this->withProgressBar($ids, fn (int $malId) => $this->syncCharacters($malId, $perAnime, $scorer));
            $this->newLine();
        }

        $this->info('Done.');

        return self::SUCCESS;
    }

    /** @return Collection<int, int> mal_ids */
    private function syncSeason()
    {
        $ids = collect();
        $page = 1;

        do {
            $json = $this->get(sprintf('seasons/%d/%s', $this->option('year'), $this->option('season')), ['page' => $page]);
            foreach ($json['data'] ?? [] as $item) {
                $this->upsertAnime($item, ['is_seasonal' => true]);
                $ids->push($item['mal_id']);
            }
            $page++;
        } while ($json && ($json['pagination']['has_next_page'] ?? false));

        return $ids;
    }

    /** @return Collection<int, int> mal_ids */
    private function syncTop(int $limit)
    {
        $ids = collect();
        $page = 1;

        while ($ids->count() < $limit) {
            $json = $this->get('top/anime', ['page' => $page, 'limit' => 25]);
            foreach ($json['data'] ?? [] as $item) {
                if ($ids->count() >= $limit) {
                    break;
                }
                $this->upsertAnime($item, ['is_top' => true]);
                $ids->push($item['mal_id']);
            }
            if (! $json || ! ($json['pagination']['has_next_page'] ?? false)) {
                break;
            }
            $page++;
        }

        return $ids;
    }

    /** @param  array<string, mixed>  $extra */
    private function upsertAnime(array $item, array $extra): Anime
    {
        return Anime::updateOrCreate(['mal_id' => $item['mal_id']], [
            'title' => $item['title'],
            'title_english' => $item['title_english'] ?? null,
            'image_url' => $item['images']['jpg']['large_image_url'] ?? $item['images']['jpg']['image_url'] ?? null,
            'type' => $item['type'] ?? null,
            'episodes' => $item['episodes'] ?? null,
            'score' => $item['score'] ?? null,
            'rank' => $item['rank'] ?? null,
            'popularity' => $item['popularity'] ?? null,
            'members' => $item['members'] ?? null,
            'favorites' => $item['favorites'] ?? null,
            'season' => $item['season'] ?? null,
            'year' => $item['year'] ?? null,
        ] + $extra);
    }

    private function syncCharacters(int $animeMalId, int $limit, RedFlagScorer $scorer): void
    {
        $anime = Anime::where('mal_id', $animeMalId)->first();
        $json = $anime ? $this->get("anime/{$animeMalId}/characters") : null;

        if (! $json) {
            return;
        }

        $entries = collect($json['data'] ?? [])
            ->sortByDesc('favorites')
            ->take($limit);

        foreach ($entries as $entry) {
            $c = $entry['character'];
            $character = Character::firstOrNew(['mal_id' => $c['mal_id']]);

            $character->fill([
                'anime_id' => $anime->id,
                'name' => $c['name'],
                'image_url' => $c['images']['jpg']['image_url'] ?? null,
                'role' => $entry['role'] ?? null,
                'favorites' => $entry['favorites'] ?? 0,
            ]);

            // Tags need the character bio, which is a separate request. Fetch once.
            if ($character->tags === null) {
                $detail = $this->get("characters/{$c['mal_id']}");
                if ($detail) {
                    $character->tags = $scorer->extractTags($detail['data']['about'] ?? null);
                }
            }

            $character->save();
        }
    }

    /** @return array<string, mixed>|null */
    private function get(string $path, array $query = []): ?array
    {
        $delay = (int) $this->option('delay');
        if ($delay > 0) {
            usleep($delay * 1000);
        }

        /** @var PendingRequest $http */
        $http = Http::baseUrl(config('services.jikan.base_url', 'https://api.jikan.moe/v4'))
            ->acceptJson()
            ->timeout(20)
            ->retry(3, 1000, throw: false);

        $response = $http->get($path, $query);

        if ($response->failed()) {
            $this->warn("Request failed ({$response->status()}): {$path}");

            return null;
        }

        return $response->json();
    }
}
