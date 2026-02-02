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

    //gửi tin nhắn
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
            'conversation_id' => $request->input('conversation_id'),
            'attachment' => $request->input('attachments') ?? null,
        ]);

        broadcast(new \App\Events\MessageSent(
            $request->input('conversation_id'),
            $message
            ))->toOthers();

        return response()->json(['status' => 'Message Sent!', 'message' => $message]);
    }

    // Lấy tin nhắn cũ hơn (Load Previous - Kéo lên trên)
    public function getOlderMessages($conversationID, $MessageID = null)
    {
        $query = Message::where('conversation_id', $conversationID);

        if ($MessageID) {
            $query->where('id', '<', $MessageID);
        }

        $messages = $query->orderBy('id', 'desc') // Lấy những tin sát mốc thời gian nhất
                        ->limit(20)             
                        ->get();


        return response()->json($messages->reverse()->values());
    }

    // Lấy tin nhắn mới hơn (Load Next - Kéo xuống dưới)
    public function getNewerMessages($conversationID, $MessageID)
    {
        
        $messages = Message::where('conversation_id', $conversationID)
            ->where('id', '>', $MessageID)
            ->orderBy('id', 'asc') 
            ->limit(20)
            ->get();

        return response()->json($messages->values());
    }

    //lấy danh sách cuộc trò chuyện của người dùng
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

    //tạo cuộc trò chuyện mới
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

    //tìm kiếm tin nhắn trong tất cả các cuộc trò chuyện của người dùng
    public function searchMessages(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:255',
        ]);

        $userId = Auth::id();
        $query = $request->input('query');

       $messages = Message::where(function ($q) use ($userId) {
            $q->where('sender_id', $userId)
              ->orWhere('receiver_id', $userId);
        })
        ->where('message', 'LIKE', "%{$query}%")
        ->orderBy('created_at', 'desc')
        ->paginate(20  );

        return response()->json($messages);
    }

    //lấy tin nhắn xung quanh tin nhắn được tìm thấy
    public function fetchSearchedMessages($conversationId, $messageId)
    {
        $userId = Auth::id();

        $conversation = Conversation::where('id', $conversationId)
            ->where(fn ($q) =>
                $q->where('user1_id', $userId)
                ->orWhere('user2_id', $userId)
            )
            ->exists();

        $baseQuery = Message::with([
            'sender:id,username,avatar',
            'receiver:id,username,avatar',
        ])->where('conversation_id', $conversationId);

        // Older messages
        $older = (clone $baseQuery)
            ->where('id', '<', $messageId)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        // Anchor
        $anchor = (clone $baseQuery)
            ->where('id', $messageId)
            ->firstOrFail();

        // Newer messages
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

}
