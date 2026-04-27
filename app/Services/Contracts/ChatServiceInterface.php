<?php

namespace App\Services\Contracts;

use Illuminate\Support\Collection;

interface ChatServiceInterface
{
    public function getHistoryForUser(int $userId, int $limit = 50): Collection;

    public function processUserMessage(int $userId, string $message): array;
}
