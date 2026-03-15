<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveOverviewUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly array $overview,
    ) {
    }

    public function broadcastOn(): array
    {
        return [new Channel('live.overview')];
    }

    public function broadcastAs(): string
    {
        return 'live.overview.updated';
    }

    public function broadcastWith(): array
    {
        return $this->overview;
    }
}
