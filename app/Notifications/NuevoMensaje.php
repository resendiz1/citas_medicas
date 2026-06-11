<?php

namespace App\Notifications;

use App\Models\CitaMedica;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NuevoMensaje extends Notification
{
    use Queueable;

    public function __construct(
        protected User $remitente,
        protected CitaMedica $cita,
        protected string $mensaje,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'cita_id' => $this->cita->id,
            'tipo' => 'mensaje',
            'message' => 'Nuevo mensaje de ' . $this->remitente->name . ': ' . $this->mensaje,
            'remitente' => $this->remitente->name,
            'fecha' => $this->cita->fecha_hora->format('d/m/Y H:i'),
        ];
    }
}
