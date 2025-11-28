<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class NewMessage implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public $jid;
    public $message;

    public function __construct($jid, $message)
    {
        $this->jid = $jid;
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new Channel('chat.' . $this->jid);
    }

    public function broadcastWith()
    {
        return [
            'jid' => $this->jid,
            'message' => $this->message,
            'generated_at' => now()->toDateTimeString(),
        ];
    }
}
