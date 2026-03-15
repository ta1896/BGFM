<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MatchStateUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $matchId,
        public readonly array $payload,
    ) {
    }

    public function broadcastOn(): array
    {
        return [new Channel('match.'.$this->matchId)];
    }

    public function broadcastAs(): string
    {
        return 'match.state.updated';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
