<?php

namespace App\Notifications;

use App\Models\Pedido;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EstadoPedido extends Notification
{
    public function __construct(public Pedido $pedido) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('Pedido #'.$this->pedido->id.' · ByteStore')
            ->greeting('Hola '.$notifiable->name)
            ->line('Tu pedido #'.$this->pedido->id.' está en estado '.$this->pedido->estado_visible.'.')
            ->line($this->pedido->entrega_cotizada
                ? 'Total: S/ '.number_format($this->pedido->total, 2)
                : 'El costo de envío está pendiente de cotización. Aún no realices el pago.')
            ->action('Ver mi pedido', route('mis-pedidos.show', $this->pedido))
            ->salutation('ByteStore');
    }
}
