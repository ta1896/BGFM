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
            $autoAccept = $challenged->is_cpu || !$challenged->user_id;

            $request = FriendlyMatchRequest::create([
                'challenger_club_id' => $challenger->id,
                'challenged_club_id' => $challenged->id,
                'requested_by_user_id' => $actor->id,
                'kickoff_at' => $kickoffAt,
                'stadium_club_id' => $challenger->id,
                'status' => $autoAccept ? 'auto_accepted' : 'pending',
                'message' => $message,
                'responded_at' => $autoAccept ? now() : null,
            ]);

            $match = null;
            if ($autoAccept) {
                $match = $this->createFriendlyMatch($challenger, $challenged, $kickoffAt);
                $request->update(['accepted_match_id' => $match->id]);
            } else {
                if ($challenged->user_id) {
                    GameNotification::create([
                        'user_id' => $challenged->user_id,
                        'club_id' => $challenged->id,
                        'type' => 'friendly_request',
                        'title' => 'Freundschaftsspiel-Anfrage',
                        'message' => $challenger->name.' moechte ein Freundschaftsspiel am '.$kickoffAt->format('d.m.Y H:i').' spielen.',
                        'action_url' => '/friendlies?club='.$challenged->id,
                    ]);
                }
            }

            return [
                'type' => $autoAccept ? 'auto_accepted' : 'pending',
                'request' => $request->fresh(['challengerClub', 'challengedClub', 'acceptedMatch']),
                'match' => $match,
            ];
        });
    }

    public function acceptRequest(FriendlyMatchRequest $request, User $actor): GameMatch
    {
        abort_if($request->status !== 'pending', 422, 'Diese Anfrage ist nicht mehr offen.');

        return DB::transaction(function () use ($request, $actor): GameMatch {
            $request->loadMissing(['challengerClub', 'challengedClub']);

            $match = $this->createFriendlyMatch(
                $request->challengerClub,
                $request->challengedClub,
                Carbon::parse($request->kickoff_at)
            );

            $request->update([
                'status' => 'accepted',
                'accepted_match_id' => $match->id,
                'responded_at' => now(),
            ]);

            if ($request->challengerClub->user_id) {
                GameNotification::create([
                    'user_id' => $request->challengerClub->user_id,
                    'club_id' => $request->challengerClub->id,
                    'type' => 'friendly_request_accepted',
                    'title' => 'Freundschaftsspiel bestaetigt',
                    'message' => $request->challengedClub->name.' hat die Anfrage angenommen.',
                    'action_url' => '/matches/'.$match->id,
                ]);
            }

            if ($actor->id !== (int) $request->requested_by_user_id) {
                GameNotification::create([
                    'user_id' => $actor->id,
                    'club_id' => $request->challenged_club_id,
                    'type' => 'friendly_request_handled',
                    'title' => 'Freundschaftsspiel bestaetigt',
                    'message' => 'Die Anfrage wurde angenommen und terminiert.',
                    'action_url' => '/matches/'.$match->id,
                ]);
            }

            return $match;
        });
    }

    public function rejectRequest(FriendlyMatchRequest $request): void
    {
        abort_if($request->status !== 'pending', 422, 'Diese Anfrage ist nicht mehr offen.');

        $request->loadMissing(['challengerClub', 'challengedClub']);

        DB::transaction(function () use ($request): void {
            $request->update([
                'status' => 'rejected',
                'responded_at' => now(),
            ]);

            if ($request->challengerClub->user_id) {
                GameNotification::create([
                    'user_id' => $request->challengerClub->user_id,
                    'club_id' => $request->challenger_club_id,
                    'type' => 'friendly_request_rejected',
                    'title' => 'Freundschaftsspiel abgelehnt',
                    'message' => $request->challengedClub->name.' hat die Anfrage abgelehnt.',
                    'action_url' => '/friendlies?club='.$request->challenger_club_id,
                ]);
            }
        });
    }

    private function createFriendlyMatch(Club $homeClub, Club $awayClub, Carbon $kickoffAt): GameMatch
    {
        return GameMatch::create([
            'competition_season_id' => null,
            'season_id' => null,
            'type' => 'friendly',
            'competition_context' => CompetitionContextService::FRIENDLY,
            'stage' => 'Friendly',
            'round_number' => null,
            'matchday' => null,
            'kickoff_at' => $kickoffAt,
            'status' => 'scheduled',
            'home_club_id' => $homeClub->id,
            'away_club_id' => $awayClub->id,
            'stadium_club_id' => $homeClub->id,
            'simulation_seed' => random_int(10000, 99999),
        ]);
    }
}
