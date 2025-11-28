<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class NewChat implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public $jid;
    public $preview;

    public function __construct($jid, $preview = '')
    {
        $this->jid = $jid;
        $this->preview = $preview;
    }

    public function broadcastOn()
    {
        return new Channel('chats');
    }

    public function broadcastWith()
    {
        return [
            'jid' => $this->jid,
            'preview' => $this->preview,
            'generated_at' => now()->toDateTimeString(),
        ];
    }
}
