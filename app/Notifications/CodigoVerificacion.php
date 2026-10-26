<?php

namespace App\Notifications;

use App\Services\CorreoVerificacion;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CodigoVerificacion extends Notification
{
    public function __construct(public readonly string $codigo) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tu código de verificación de ByteStore')
            ->view(['html' => 'emails.verificacion', 'text' => 'emails.verificacion-texto'], [
                'nombre' => $notifiable->name,
                'codigo' => $this->codigo,
                'expiresMinutes' => CorreoVerificacion::MINUTOS_VALIDEZ,
            ]);
    }
}
