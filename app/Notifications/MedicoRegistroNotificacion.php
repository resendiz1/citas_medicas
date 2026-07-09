<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MedicoRegistroNotificacion extends Notification
{
    use Queueable;

    public function __construct(
        protected User $medico,
        protected string $tipo,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        if ($this->tipo === 'nuevo_registro') {
            return (new MailMessage)
                ->subject('Nuevo médico registrado — Pendiente de revisión')
                ->greeting('Hola Admin')
                ->line('El Dr/a. ' . $this->medico->name . ' se ha registrado en la plataforma.')
                ->line('Correo: ' . $this->medico->email)
                ->line('Debes revisar su perfil y aprobar su cuenta para que pueda ser visible para los pacientes.')
                ->action('Revisar solicitud', route('admin.medicos'));
        }

        if ($this->tipo === 'pendiente') {
            return (new MailMessage)
                ->subject('Registro recibido — Pendiente de revisión')
                ->greeting('Hola Dr/a. ' . $this->medico->name)
                ->line('Hemos recibido tu solicitud de registro en Citas Médicas.')
                ->line('Un administrador está revisando tu información. Te notificaremos por correo cuando tu cuenta haya sido aprobada.')
                ->line('Este proceso suele tomar menos de 24 horas hábiles.')
                ->action('Ir a la plataforma', route('dashboard'));
        }

        return (new MailMessage)
            ->subject('¡Tu cuenta ha sido aprobada!')
            ->greeting('Hola Dr/a. ' . $this->medico->name)
            ->line('Tu cuenta de médico en Citas Médicas ha sido aprobada.')
            ->line('Ahora los pacientes podrán ver tu perfil y agendar citas contigo.')
            ->line('Ya puedes configurar tus horarios y empezar a recibir pacientes.')
            ->action('Iniciar sesión', route('dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        $message = match ($this->tipo) {
            'nuevo_registro' => 'Nuevo médico registrado: ' . $this->medico->name . ' — Pendiente de revisión',
            'pendiente' => 'Tu registro está pendiente de revisión por un administrador.',
            'aprobado' => '¡Tu cuenta ha sido aprobada! Ya puedes recibir pacientes.',
            default => 'Estado de registro actualizado.',
        };

        return [
            'medico_id' => $this->medico->id,
            'medico_nombre' => $this->medico->name,
            'tipo' => $this->tipo,
            'message' => $message,
        ];
    }
}
