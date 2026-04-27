<?php

namespace App\Repositories\Eloquent;

use App\Models\Chat;
use App\Repositories\Contracts\ChatRepositoryInterface;
use Illuminate\Support\Collection;

class ChatRepository implements ChatRepositoryInterface
{
    public function storeChatMessage(int $userId, string $message): Chat
    {
        return Chat::query()->create([
            'user_id' => $userId,
            'message' => $message,
            'response' => null,
        ]);
    }

    public function storeAiResponse(int $chatId, string $response): Chat
    {
        $chat = Chat::query()->findOrFail($chatId);
        $chat->update([
            'response' => $response,
        ]);

        return $chat->fresh();
    }

    public function getUserChatHistory(int $userId, int $limit = 50): Collection
    {
        return Chat::query()
            ->where('user_id', $userId)
            ->orderBy('created_at')
            ->limit($limit)
            ->get();
    }
}
