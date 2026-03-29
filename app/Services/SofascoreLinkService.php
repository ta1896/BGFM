<?php

namespace App\Services;

use App\Models\Player;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SofascoreLinkService
{
    public function linkPlayer(Player $player): array
    {
        $query = trim($player->full_name);
        if ($query === '') {
            return ['linked' => false, 'reason' => 'missing_name'];
        }

        $clubName = trim((string) optional($player->club)->name);

        $response = Http::timeout(15)->get('https://www.sofascore.com/api/v1/search/all', [
            'q' => $query,
        ]);

        if (!$response->successful()) {
            return [
                'linked' => false,
                'reason' => 'api_failed',
                'status' => $response->status(),
            ];
        }

        $results = collect($response->json('results', []))
            ->filter(fn ($result) => ($result['type'] ?? null) === 'player')
            ->values();

        if ($results->isEmpty()) {
            return ['linked' => false, 'reason' => 'not_found'];
        }

        $bestMatch = $results
            ->map(function (array $result) use ($player, $clubName) {
                $entity = $result['entity'] ?? [];
                $resultName = trim((string) ($entity['name'] ?? ''));
                $resultClub = trim((string) data_get($entity, 'team.name', ''));

                $score = 0;

                if ($this->normalize($resultName) === $this->normalize($player->full_name)) {
                    $score += 100;
                } elseif (Str::contains($this->normalize($resultName), $this->normalize($player->last_name))) {
                    $score += 40;
                }

                if ($clubName !== '' && $resultClub !== '') {
                    $normalizedClub = $this->normalize($clubName);
                    $normalizedResultClub = $this->normalize($resultClub);

                    if ($normalizedClub === $normalizedResultClub) {
                        $score += 80;
                    } elseif (
                        Str::contains($normalizedClub, $normalizedResultClub) ||
                        Str::contains($normalizedResultClub, $normalizedClub)
                    ) {
                        $score += 40;
                    }
                }

                return [
                    'score' => $score,
                    'entity' => $entity,
                    'club' => $resultClub,
                    'name' => $resultName,
                ];
            })
            ->sortByDesc('score')
            ->first();

        if (!$bestMatch || ($bestMatch['score'] ?? 0) < 80) {
            return ['linked' => false, 'reason' => 'no_confident_match'];
        }

        $id = (string) ($bestMatch['entity']['id'] ?? '');
        if ($id === '') {
            return ['linked' => false, 'reason' => 'missing_id'];
        }

        $player->update([
            'sofascore_id' => $id,
        ]);

        return [
            'linked' => true,
            'id' => $id,
            'match_name' => $bestMatch['name'] ?? null,
            'match_club' => $bestMatch['club'] ?? null,
        ];
    }

    private function normalize(string $value): string
    {
        return Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->trim()
            ->value();
    }
}
