<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\GameMatch;
use App\Models\Lineup;
use App\Models\Player;
use App\Services\MatchLineupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MatchLineupServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_clones_active_lineup_into_match_lineup_and_resolves_starters(): void
    {
        $club = $this->createClub('Alpha FC');
        $opponent = $this->createClub('Beta FC');
        $match = GameMatch::query()->create([
            'home_club_id' => $club->id,
            'away_club_id' => $opponent->id,
            'type' => 'league',
            'status' => 'scheduled',
        ]);

        $players = collect(range(1, 13))->map(function (int $index) use ($club): Player {
            return Player::query()->create([
                'club_id' => $club->id,
                'first_name' => 'P'.$index,
                'last_name' => 'Test',
                'position' => $index === 1 ? 'TW' : ($index < 6 ? 'IV' : ($index < 10 ? 'ZM' : 'MS')),
                'position_main' => $index === 1 ? 'TW' : ($index < 6 ? 'IV' : ($index < 10 ? 'ZM' : 'MS')),
                'age' => 24,
                'overall' => 80 - $index,
                'pace' => 60,
                'shooting' => 60,
                'passing' => 60,
                'defending' => 60,
                'physical' => 60,
                'stamina' => 80,
                'morale' => 75,
                'market_value' => 1000000,
            ]);
        });

        $source = Lineup::query()->create([
            'club_id' => $club->id,
            'name' => 'Default',
            'formation' => '4-3-3',
            'is_active' => true,
            'is_template' => false,
            'mentality' => 'offensive',
            'aggression' => 'normal',
            'line_height' => 'high',
            'attack_focus' => 'center',
        ]);

        $pivot = [];
        foreach ($players->take(11)->values() as $index => $player) {
            $pivot[$player->id] = [
                'pitch_position' => 'SLOT-'.$index,
                'sort_order' => $index + 1,
                'x_coord' => null,
                'y_coord' => null,
                'is_captain' => $index === 0,
                'is_set_piece_taker' => false,
                'is_bench' => false,
                'bench_order' => null,
            ];
        }
        foreach ($players->slice(11)->values() as $index => $player) {
            $pivot[$player->id] = [
                'pitch_position' => 'BANK-'.($index + 1),
                'sort_order' => 100 + $index + 1,
                'x_coord' => null,
                'y_coord' => null,
                'is_captain' => false,
                'is_set_piece_taker' => false,
                'is_bench' => true,
                'bench_order' => $index + 1,
            ];
        }
        $source->players()->sync($pivot);

        $service = $this->app->make(MatchLineupService::class);
        $lineup = $service->ensureMatchLineup($match, $club);
        $starters = $service->resolveStarters($club, $match, true);
        $participants = $service->resolveParticipants($club, $match, true);

        $this->assertSame($match->id, $lineup->match_id);
        $this->assertSame('offensive', $lineup->mentality);
        $this->assertCount(11, $starters);
        $this->assertCount(13, $participants);
        $this->assertTrue($participants->contains('id', $players->first()->id));
    }

    private function createClub(string $name): Club
    {
        return Club::query()->create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'short_name' => Str::upper(Str::substr($name, 0, 3)),
            'league' => 'Test League',
        ]);
    }
}
