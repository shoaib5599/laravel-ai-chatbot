<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChatMessageRequest;
use App\Services\Contracts\ChatServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function __construct(private readonly ChatServiceInterface $chatService) {}

    public function index(Request $request): View
    {
        $chats = $this->chatService->getHistoryForUser((int) $request->user()->id);

        return view('chat.index', compact('chats'));
    }

    public function sendMessage(ChatMessageRequest $request): JsonResponse
    {
        try {
            $chat = $this->chatService->processUserMessage(
                (int) $request->user()->id,
                $request->validated('message')
            );

            return response()->json($chat);
        } catch (\RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 503);
        }
    }
}
