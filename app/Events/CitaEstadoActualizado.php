<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class CitaEstadoActualizado implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(
        public int $citaId,
        public string $estado,
        public ?string $estadoAnterior = null,
    ) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('chat.cita.' . $this->citaId);
    }

    public function broadcastAs(): string
    {
        return 'CitaEstadoActualizado';
    }

    public function broadcastWith(): array
    {
        return [
            'cita_id'        => $this->citaId,
            'estado'         => $this->estado,
            'estado_anterior' => $this->estadoAnterior,
        ];
    }
}
