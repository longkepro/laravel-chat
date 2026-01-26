<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Pail\ValueObjects\Origin\Console;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use App\Models\MessageRead;

class ChatController extends Controller
{

    public function sendMessage(Request $request)
    {
        $rules = [
            'sender_id' => 'required|exists:users,id',
            'conversation_id' => 'required|exists:conversations,id',
            'message' => 'required|string|max:255',
            'attachment' => 'nullable|url|max:255',
        ];

        $request->validate($rules);
        $conversation = Conversation::findOrFail($request->input('conversation_id'));
        $receiverId = $conversation->user1_id == $request->input('sender_id') ? $conversation->user2_id : $conversation->user1_id;

        $message = Message::create([
            'sender_id' => $request->input('sender_id'),
            'receiver_id' => $receiverId,
            'message' => $request->input('message'),
            'attachment' => $request->input('attachments') ?? null,
        ]);

        broadcast(new \App\Events\MessageSent(
            $request->input('conversation_id'),
            $message
            ))->toOthers();

        return response()->json(['status' => 'Message Sent!', 'message' => $message]);
    }

    public function getMessages($conversationID)
    {
        $conversation = Conversation::findOrFail($conversationID);
        $messages = Message::where(function ($q) use ($conversation) {
            $q->where('sender_id', $conversation->user1_id)
              ->where('receiver_id', $conversation->user2_id);
        })->orWhere(function ($q) use ($conversation) {
            $q->where('sender_id', $conversation->user2_id)
              ->where('receiver_id', $conversation->user1_id);
        })
        ->orderBy('created_at', 'asc')
        ->paginate(20);

        return response()->json($messages);
    }

    public function getConversationList(int $perPage = 15)
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
            ->paginate($perPage);

        $conversations->getCollection()->transform(function ($conversation) use ($userId) {
            $receiver = $conversation->user1_id === $userId
                ? $conversation->userTwo
                : $conversation->userOne;

            return [
                'conversation_id' => $conversation->id,
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
        $rules = [
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id|different:user1_id',
            'message' => 'required|string|max:255',
        ];

        $request->validate($rules);
        if($request->input('sender_id') > $request->input('receiver_id')){
            //đảm bảo user1_id luôn nhỏ hơn user2_id để tránh trùng lặp conversation
            $user1_id = $request->input('receiver_id');
            $user2_id = $request->input('sender_id');
        } else {
            $user1_id = $request->input('sender_id');
            $user2_id = $request->input('receiver_id');
        }

        $conversation = Conversation::firstOrCreate(
            [
                'user1_id' => $user1_id,
                'user2_id' => $user2_id,
            ]
        );
        $request->merge(['conversation_id' => $conversation->id]);
        $this->sendMessage($request);
    }
}
