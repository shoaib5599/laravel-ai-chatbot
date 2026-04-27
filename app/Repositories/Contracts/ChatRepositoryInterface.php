<?php

namespace App\Repositories\Contracts;

use App\Models\Chat;
use Illuminate\Support\Collection;

interface ChatRepositoryInterface
{
    public function storeChatMessage(int $userId, string $message): Chat;

    public function storeAiResponse(int $chatId, string $response): Chat;

    public function getUserChatHistory(int $userId, int $limit = 50): Collection;
}
