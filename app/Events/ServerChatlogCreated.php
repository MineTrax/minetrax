<?php

namespace App\Events;

use App\Models\ServerChatlog;
use App\Utils\Helpers\MinecraftColorUtils;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServerChatlogCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ServerChatlog $serverChatlog)
    {
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('chatlogs.'.$this->serverChatlog->server_id);
    }

    public function broadcastWith()
    {
        $this->serverChatlog->data = MinecraftColorUtils::convertToHTML($this->serverChatlog->data);

        return ['data' => $this->serverChatlog];
    }
}
