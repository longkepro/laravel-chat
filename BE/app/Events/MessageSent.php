<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $conversationID;

    /**
     * Create a new event instance.
     */
    public function __construct( $conversationID, $message)
    {
        $this->message = $message;
        $this->conversationID = $conversationID;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel("chat.{$this->conversationID}"),
        ];
    }
    public function broadcastAs()
    {
        return 'MessageSent';
    }
    public function broadcastWith()
    {
        return [
            'message' => $this->message,
            'conversationID' => $this->conversationID,
        ];
    }
}
