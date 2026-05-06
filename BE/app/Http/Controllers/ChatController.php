<?php

namespace App\Http\Controllers;

use App\Events\MessageRead;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        $userId = $request->user()->id;

        $payload = $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message' => 'required|string|max:255',
            'attachment' => 'nullable|url|max:255',
        ]);

        $conversation = Conversation::findOrFail($payload['conversation_id']);

        if ($conversation->user1_id !== $userId && $conversation->user2_id !== $userId) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $receiverId = $conversation->user1_id === $userId
            ? $conversation->user2_id
            : $conversation->user1_id;

        $message = Message::create([
            'sender_id' => $userId,
            'receiver_id' => $receiverId,
            'message' => $payload['message'],
            'conversation_id' => $payload['conversation_id'],
            'attachment' => $payload['attachment'] ?? null,
        ]);

        broadcast(new \App\Events\MessageSent($payload['conversation_id'], $message))->toOthers();

        return response()->json([
            'status' => 'Message sent!',
            'message' => $message,
        ]);
    }

    public function getOlderMessages(Request $request, $conversationID, $MessageID = null)
    {
        $this->ensureConversationMember($request->user()->id, (int) $conversationID);

        $query = Message::where('conversation_id', $conversationID);

        if ($MessageID) {
            $query->where('id', '<', $MessageID);
        }

        $messages = $query
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();

        return response()->json($messages->reverse()->values());
    }

    public function getNewerMessages(Request $request, $conversationID, $MessageID)
    {
        $this->ensureConversationMember($request->user()->id, (int) $conversationID);

        $messages = Message::where('conversation_id', $conversationID)
            ->where('id', '>', $MessageID)
            ->orderBy('id', 'asc')
            ->limit(20)
            ->get();

        return response()->json($messages->values());
    }

    public function getConversationList()
    {
        $userId = Auth::id();

        $conversations = Conversation::with([
            'lastMessage',
            'userOne:id,username,avatar',
            'userTwo:id,username,avatar',
        ])
            ->where(function ($q) use ($userId) {
                $q->where('user1_id', $userId)
                    ->orWhere('user2_id', $userId);
            })
            ->latest('updated_at')
            ->paginate(20);

        $conversations->getCollection()->transform(function ($conversation) use ($userId) {
            $receiver = $conversation->user1_id === $userId
                ? $conversation->userTwo
                : $conversation->userOne;

            return [
                'conversation_id' => $conversation->id,
                'user1_id' => $conversation->user1_id,
                'user2_id' => $conversation->user2_id,
                'last_message_id1' => $conversation->last_message_id1,
                'last_message_id2' => $conversation->last_message_id2,
                'receiver' => $receiver ? [
                    'id' => $receiver->id,
                    'username' => $receiver->username,
                    'avatar' => $receiver->avatar,
                ] : null,
                'last_message' => $conversation->lastMessage,
                'last_read_message_id' => $conversation->user1_id === $userId
                    ? $conversation->last_message_id1
                    : $conversation->last_message_id2,
                'updated_at' => $conversation->updated_at,
            ];
        });

        return response()->json($conversations);
    }

    public function createConversation(Request $request)
    {
        $userId = $request->user()->id;

        $payload = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:255',
        ]);

        if ((int) $payload['receiver_id'] === $userId) {
            return response()->json(['error' => 'Invalid receiver'], 422);
        }

        if ($userId > $payload['receiver_id']) {
            $user1_id = $payload['receiver_id'];
            $user2_id = $userId;
        } else {
            $user1_id = $userId;
            $user2_id = $payload['receiver_id'];
        }

        $conversation = Conversation::firstOrCreate([
            'user1_id' => $user1_id,
            'user2_id' => $user2_id,
        ]);

        $request->merge([
            'conversation_id' => $conversation->id,
            'message' => $payload['message'],
        ]);

        return $this->sendMessage($request);
    }

    public function searchMessages(Request $request)
    {
        $payload = $request->validate([
            'q' => 'required|string|max:255',
        ]);

        $userId = Auth::id();
        $query = $payload['q'];

        $messages = Message::where(function ($q) use ($userId) {
            $q->where('sender_id', $userId)
                ->orWhere('receiver_id', $userId);
        })
            ->where('message', 'LIKE', "%{$query}%")
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($messages);
    }

    public function fetchSearchedMessages($conversationId, $messageId)
    {
        $userId = Auth::id();
        $this->ensureConversationMember($userId, (int) $conversationId);

        $baseQuery = Message::with([
            'sender:id,username,avatar',
            'receiver:id,username,avatar',
        ])->where('conversation_id', $conversationId);

        $older = (clone $baseQuery)
            ->where('id', '<', $messageId)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        $anchor = (clone $baseQuery)
            ->where('id', $messageId)
            ->firstOrFail();

        $newer = (clone $baseQuery)
            ->where('id', '>', $messageId)
            ->orderBy('id', 'asc')
            ->limit(10)
            ->get();

        return response()->json([
            'anchor_id' => $messageId,
            'messages' => $older
                ->reverse()
                ->push($anchor)
                ->merge($newer)
                ->values(),
            'has_older' => $older->count() === 10,
            'has_newer' => $newer->count() === 10,
        ]);
    }

    public function markAsRead(Request $request, $conversationID)
    {
        $payload = $request->validate([
            'message_id' => 'required|integer',
        ]);

        $userId = Auth::id();
        $conversation = Conversation::findOrFail($conversationID);

        if ($conversation->user1_id !== $userId && $conversation->user2_id !== $userId) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $messageId = (int) $payload['message_id'];
        $messageExists = Message::where('id', $messageId)
            ->where('conversation_id', $conversationID)
            ->exists();

        if (! $messageExists) {
            return response()->json(['error' => 'Invalid message'], 422);
        }

        $updates = [];
        if ($conversation->user1_id === $userId) {
            if (empty($conversation->last_message_id1) || $messageId > $conversation->last_message_id1) {
                $updates['last_message_id1'] = $messageId;
            }
        } else {
            if (empty($conversation->last_message_id2) || $messageId > $conversation->last_message_id2) {
                $updates['last_message_id2'] = $messageId;
            }
        }

        if (! empty($updates)) {
            Conversation::where('id', $conversationID)->update($updates);
            $conversation->refresh();
            broadcast(new MessageRead(
                $conversationID,
                $userId,
                $conversation->last_message_id1,
                $conversation->last_message_id2
            ))->toOthers();
        }

        return response()->json([
            'status' => 'ok',
            'conversation_id' => $conversationID,
            'last_message_id1' => $conversation->last_message_id1,
            'last_message_id2' => $conversation->last_message_id2,
        ]);
    }

    protected function ensureConversationMember(int $userId, int $conversationId): void
    {
        Conversation::where('id', $conversationId)
            ->where(fn ($q) => $q->where('user1_id', $userId)->orWhere('user2_id', $userId))
            ->firstOrFail();
    }
}
