<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class SessionDeleted implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public $timestamp;

    public function __construct()
    {
        $this->timestamp = now()->toDateTimeString();
    }

    public function broadcastOn()
    {
        return new Channel('session-events');
    }

    public function broadcastWith()
    {
        return ['deleted_at' => $this->timestamp];
    }
}
