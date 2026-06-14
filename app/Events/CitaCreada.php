<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class CitaCreada implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(
        public int $userId,
        public array $data,
    ) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('App.Models.User.' . $this->userId);
    }

    public function broadcastAs(): string
    {
        return 'CitaCreada';
    }

    public function broadcastWith(): array
    {
        return $this->data;
    }
}
