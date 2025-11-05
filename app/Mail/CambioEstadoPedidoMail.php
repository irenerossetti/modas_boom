<?php

namespace App\Mail;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CambioEstadoPedidoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pedido;
    public $estadoAnterior;
    public $estadoNuevo;

    /**
     * Create a new message instance.
     */
    public function __construct(Pedido $pedido, string $estadoAnterior, string $estadoNuevo)
    {
        $this->pedido = $pedido;
        $this->estadoAnterior = $estadoAnterior;
        $this->estadoNuevo = $estadoNuevo;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Actualización de su pedido #' . $this->pedido->id_pedido . ' - Modas Boom',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.cambio-estado-pedido',
            with: [
                'pedido' => $this->pedido,
                'cliente' => $this->pedido->cliente,
                'estadoAnterior' => $this->estadoAnterior,
                'estadoNuevo' => $this->estadoNuevo,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
