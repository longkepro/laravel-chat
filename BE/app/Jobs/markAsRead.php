<?php

namespace App\Jobs;

use App\Models\Conversation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Auth;


class markAsRead implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public $ConversationID;
    public $lastMessageId;
    public function __construct($ConversationID, $lastMessageId = null)
    {
        $this->ConversationID = $ConversationID;
        $this->lastMessageId = $lastMessageId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
       $senderId = Auth::id();
        $userOne = Conversation::where('id', $this->ConversationID)->value('user1_id');

        $valuesToUpdate = [
            'updated_at' => now(), // Luôn cập nhật thời gian hoạt động mới nhất
        ];

        // Chỉ cập nhật last_message_id nếu có giá trị
        if (!empty($this->lastMessageId)) {
            $valuesToUpdate['last_message_id'] = $this->lastMessageId;
            if ($userOne == $senderId) {
                $valuesToUpdate['last_message_id1'] = $this->lastMessageId;
            } else {
                $valuesToUpdate['last_message_id2'] = $this->lastMessageId;
            }
        }

        Conversation::updateOrCreate(
            [
                'id' => $this->ConversationID
            ],
            $valuesToUpdate // Dữ liệu để cập nhật hoặc tạo mới
        );//vì mỗi conversation chỉ có 1 cặp user_one_id và user_two_id, nên không cần conversation_id để cập nhật
    }
}
