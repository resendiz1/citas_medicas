<?php

namespace App\Notifications;

use App\Models\CitaMedica;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CitaEstadoNotificacion extends Notification
{
    use Queueable;

    public function __construct(
        protected CitaMedica $cita,
        protected string $tipo,
        protected ?string $estadoAnterior = null,
        protected ?string $estadoNuevo = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        if ($this->tipo === 'creada') {
            return (new MailMessage)
                ->subject('Nueva cita asignada')
                ->greeting('Hola Dr/a. ' . $notifiable->name)
                ->line('Se ha agendado una nueva cita médica.')
                ->line('Paciente: ' . $this->cita->paciente->name)
                ->line('Fecha: ' . $this->cita->fecha_hora->format('d/m/Y H:i'))
                ->line('Motivo: ' . $this->cita->motivo)
                ->action('Ver cita', route('dashboard'));
        }

        if ($this->tipo === 'reprogramacion_confirmada') {
            return (new MailMessage)
                ->subject('Reprogramación confirmada por el paciente')
                ->greeting('Hola Dr/a. ' . $notifiable->name)
                ->line('El paciente ' . $this->cita->paciente->name . ' ha confirmado la reprogramación de la cita.')
                ->line('Nueva fecha: ' . $this->cita->fecha_hora->format('d/m/Y H:i'))
                ->action('Ver cita', route('dashboard'));
        }

        if ($this->tipo === 'reprogramacion_rechazada') {
            return (new MailMessage)
                ->subject('Reprogramación rechazada por el paciente')
                ->greeting('Hola Dr/a. ' . $notifiable->name)
                ->line('El paciente ' . $this->cita->paciente->name . ' ha rechazado la reprogramación de la cita.')
                ->line('La cita mantiene la fecha original: ' . $this->cita->fecha_hora->format('d/m/Y H:i'))
                ->action('Ver cita', route('dashboard'));
        }

        return (new MailMessage)
            ->subject('Estado de cita actualizado')
            ->greeting('Hola ' . $notifiable->name)
            ->line('El estado de tu cita del ' . $this->cita->fecha_hora->format('d/m/Y H:i') . ' ha cambiado.')
            ->line('Estado anterior: ' . ($this->estadoAnterior ?? '—'))
            ->line('Estado nuevo: ' . ($this->estadoNuevo ?? '—'))
            ->action('Ver cita', route('dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        $message = match ($this->tipo) {
            'creada' => 'Nueva cita asignada: ' . $this->cita->paciente->name . ' - ' . $this->cita->fecha_hora->format('d/m/Y H:i'),
            'reprogramacion_confirmada' => $this->cita->paciente->name . ' confirmó la reprogramación - ' . $this->cita->fecha_hora->format('d/m/Y H:i'),
            'reprogramacion_rechazada' => $this->cita->paciente->name . ' rechazó la reprogramación',
            default => 'Cita #' . $this->cita->id . ': ' . ($this->estadoAnterior ?? '—') . ' → ' . ($this->estadoNuevo ?? '—'),
        };

        return [
            'cita_id' => $this->cita->id,
            'tipo' => $this->tipo,
            'message' => $message,
            'paciente' => $this->cita->paciente->name ?? null,
            'medico' => $this->cita->medico->name ?? null,
            'fecha' => $this->cita->fecha_hora->format('d/m/Y H:i'),
        ];
    }
}
