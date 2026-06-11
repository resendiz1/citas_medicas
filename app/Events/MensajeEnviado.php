<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class MensajeEnviado implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(
        public array $data,
        public int $citaId,
    ) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('chat.cita.' . $this->citaId);
    }

    public function broadcastAs(): string
    {
        return 'MensajeEnviado';
    }

    public function broadcastWith(): array
    {
        return $this->data;
    }
}
