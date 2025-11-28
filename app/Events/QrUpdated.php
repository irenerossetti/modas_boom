<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class QrUpdated implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public $qrBase64;

    public function __construct($qrBase64)
    {
        $this->qrBase64 = $qrBase64;
    }

    public function broadcastOn()
    {
        return new Channel('qr-updates');
    }

    public function broadcastWith()
    {
        return [
            'qr' => $this->qrBase64,
            'generated_at' => now()->toDateTimeString(),
        ];
    }
}
