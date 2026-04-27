<?php

namespace App\Services;

use App\Repositories\Contracts\ChatRepositoryInterface;
use App\Services\Contracts\AIServiceInterface;
use App\Services\Contracts\ChatServiceInterface;
use Illuminate\Support\Collection;

class ChatService implements ChatServiceInterface
{
    public function __construct(
        private readonly ChatRepositoryInterface $chatRepository,
        private readonly AIServiceInterface $aiService,
        private readonly RagService $ragService
    ) {}

    public function getHistoryForUser(int $userId, int $limit = 50): Collection
    {
        return $this->chatRepository->getUserChatHistory($userId, $limit);
    }

    public function processUserMessage(int $userId, string $message): array
    {
        $chat = $this->chatRepository->storeChatMessage($userId, $message);
        $prompt = $this->ragService->buildPromptWithContext($message);
        $aiResponse = $prompt === RagService::NO_CONTEXT_SENTINEL
            ? RagService::NO_CONTEXT_MESSAGE
            : $this->aiService->sendMessage($prompt);
        $updatedChat = $this->chatRepository->storeAiResponse((int) $chat->id, $aiResponse);

        return [
            'id' => (int) $updatedChat->id,
            'message' => (string) $updatedChat->message,
            'response' => (string) $updatedChat->response,
        ];
    }
}
