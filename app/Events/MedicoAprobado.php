<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class MedicoAprobado implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(
        public int $userId,
    ) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('App.Models.User.' . $this->userId);
    }

    public function broadcastAs(): string
    {
        return 'MedicoAprobado';
    }

    public function broadcastWith(): array
    {
        return ['aprobado' => true];
    }
}
