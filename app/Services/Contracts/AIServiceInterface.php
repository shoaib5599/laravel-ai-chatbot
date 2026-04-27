<?php

namespace App\Services\Contracts;

interface AIServiceInterface
{
    public function sendMessage(string $message): string;
}
