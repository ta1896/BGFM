<?php

namespace App\Services;

use App\Models\Club;
use App\Models\FriendlyMatchRequest;
use App\Models\GameMatch;
use App\Models\GameNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FriendlyMatchService
{
    /**
     * @return array{type:string,request:FriendlyMatchRequest,match:GameMatch|null}
     */
    public function createRequest(
        Club $challenger,
        Club $challenged,
        User $actor,
        Carbon $kickoffAt,
        ?string $message = null
    ): array {
        return DB::transaction(function () use ($challenger, $challenged, $actor, $kickoffAt, $message): array {
            $isCpu = $challenged->is_cpu || !$challenged->user_id;

            // Create the request
            $request = FriendlyMatchRequest::create([
                'challenger_club_id' => $challenger->id,
                'challenged_club_id' => $challenged->id,
                'requested_by_user_id' => $actor->id,
                'kickoff_at' => $kickoffAt,
                'stadium_club_id' => $challenger->id, // Stadium is always Challenger's home
                'status' => $isCpu ? 'auto_accepted' : 'pending',
                'message' => $message,
                'responded_at' => $isCpu ? now() : null,
            ]);

            $match = null;
            if ($isCpu) {
                // Auto-create match for CPU teams
                $match = $this->createMatchFromRequest($request);
                $request->update(['accepted_match_id' => $match->id]);
            } else {
                // Notify human opponent
                if ($challenged->user_id) {
                    GameNotification::create([
                        'user_id' => $challenged->user_id,
                        'club_id' => $challenged->id,
                        'type' => 'friendly_request',
                        'title' => 'Freundschaftsspiel-Anfrage',
                        'message' => $challenger->name . ' möchte ein Testspiel am ' . $kickoffAt->format('d.m.Y H:i') . ' bestreiten.',
                        'action_url' => '/friendlies?club=' . $challenged->id,
                    ]);
                }
            }

            return [
                'type' => $isCpu ? 'auto_accepted' : 'pending',
                'request' => $request,
                'match' => $match,
            ];
        });
    }

    public function acceptRequest(FriendlyMatchRequest $request, User $actor): GameMatch
    {
        if ($request->status !== 'pending') {
            throw new \RuntimeException('Diese Anfrage ist nicht mehr offen.');
        }

        return DB::transaction(function () use ($request, $actor): GameMatch {
            // Load relationships if missing
            if (!$request->relationLoaded('challengerClub') || !$request->relationLoaded('challengedClub')) {
                $request->load(['challengerClub', 'challengedClub']);
            }

            // Create the actual match
            $match = $this->createMatchFromRequest($request);

            // Update request status
            $request->update([
                'status' => 'accepted',
                'accepted_match_id' => $match->id,
                'responded_at' => now(),
            ]);

            // Notify Challenger
            if ($request->challengerClub->user_id) {
                GameNotification::create([
                    'user_id' => $request->challengerClub->user_id,
                    'club_id' => $request->challengerClub->id,
                    'type' => 'friendly_request_accepted',
                    'title' => 'Freundschaftsspiel bestätigt',
                    'message' => $request->challengedClub->name . ' hat die Anfrage angenommen.',
                    'action_url' => '/matches/' . $match->id,
                ]);
            }

            return $match;
        });
    }

    public function rejectRequest(FriendlyMatchRequest $request): void
    {
        if ($request->status !== 'pending') {
            throw new \RuntimeException('Diese Anfrage ist nicht mehr offen.');
        }

        DB::transaction(function () use ($request): void {
            $request->update([
                'status' => 'rejected',
                'responded_at' => now(),
            ]);

            $request->loadMissing(['challengerClub', 'challengedClub']);

            // Notify Challenger
            if ($request->challengerClub->user_id) {
                GameNotification::create([
                    'user_id' => $request->challengerClub->user_id,
                    'club_id' => $request->challenger_club_id,
                    'type' => 'friendly_request_rejected',
                    'title' => 'Freundschaftsspiel abgelehnt',
                    'message' => $request->challengedClub->name . ' hat die Anfrage abgelehnt.',
                    'action_url' => '/friendlies?club=' . $request->challenger_club_id,
                ]);
            }
        });
    }

    private function createMatchFromRequest(FriendlyMatchRequest $request): GameMatch
    {
        // Challenger is HOME, Opponent is AWAY
        // Stadium is Challenger's Stadium

        return GameMatch::create([
            'competition_season_id' => null,
            'season_id' => null,
            'type' => 'friendly',
            'competition_context' => CompetitionContextService::FRIENDLY,
            'stage' => 'Friendly',
            'round_number' => null,
            'matchday' => null,
            'kickoff_at' => $request->kickoff_at,
            'status' => 'scheduled',
            'home_club_id' => $request->challenger_club_id,
            'away_club_id' => $request->challenged_club_id,
            'stadium_club_id' => $request->challenger_club_id,
            'simulation_seed' => random_int(10000, 99999),
        ]);
    }
}
