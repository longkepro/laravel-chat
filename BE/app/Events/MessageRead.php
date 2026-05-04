<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $conversationId;
    public int $readerId;
    public ?int $lastMessageId1;
    public ?int $lastMessageId2;

    public function __construct(int $conversationId, int $readerId, ?int $lastMessageId1, ?int $lastMessageId2)
    {
        $this->conversationId = $conversationId;
        $this->readerId = $readerId;
        $this->lastMessageId1 = $lastMessageId1;
        $this->lastMessageId2 = $lastMessageId2;
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel("chat.{$this->conversationId}"),
        ];
    }

    public function broadcastAs()
    {
        return 'MessageRead';
    }

    public function broadcastWith()
    {
        return [
            'conversation_id' => $this->conversationId,
            'reader_id' => $this->readerId,
            'last_message_id1' => $this->lastMessageId1,
            'last_message_id2' => $this->lastMessageId2,
        ];
    }
}
