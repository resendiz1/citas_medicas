<?php

namespace App\Notifications;

use App\Models\BugReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BugReportRespuestaNotificacion extends Notification
{
    use Queueable;

    public BugReport $bugReport;
    public string $respuesta;
    public string $nuevoStatus;

    public function __construct(BugReport $bugReport, string $respuesta, string $nuevoStatus)
    {
        $this->bugReport = $bugReport;
        $this->respuesta = $respuesta;
        $this->nuevoStatus = $nuevoStatus;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $statusLabel = match ($this->nuevoStatus) {
            'en_revision' => 'En revisión',
            'resuelto'    => 'Resuelto',
            'rechazado'   => 'Rechazado',
            default       => ucfirst($this->nuevoStatus),
        };

        return (new MailMessage)
            ->subject("Respuesta a tu reporte: {$this->bugReport->titulo}")
            ->greeting("Hola {$notifiable->name},")
            ->line("Tu reporte «{$this->bugReport->titulo}» ha sido actualizado a: **{$statusLabel}**.")
            ->line("Mensaje del administrador:")
            ->line($this->respuesta)
            ->action('Ir al inicio', route('dashboard'))
            ->salutation('Saludos, el equipo de Citas Médicas');
    }

    public function toArray($notifiable): array
    {
        return [
            'bug_report_id' => $this->bugReport->id,
            'titulo'        => $this->bugReport->titulo,
            'respuesta'     => $this->respuesta,
            'nuevo_status'  => $this->nuevoStatus,
        ];
    }
}
