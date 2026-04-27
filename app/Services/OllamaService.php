<?php

namespace App\Services;

use App\Services\Contracts\AIServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OllamaService implements AIServiceInterface
{
    public function sendMessage(string $message): string
    {
        $baseUrl = rtrim((string) config('services.ollama.base_url', 'http://127.0.0.1:11434'), '/');
        $model = (string) config('services.ollama.model', 'llama3.2');
        $timeout = (int) config('services.ollama.timeout', 120);

        try {
            $response = Http::timeout($timeout)
                ->post("{$baseUrl}/api/generate", [
                    'model' => $model,
                    'prompt' => $message,
                    'stream' => false,
                ]);

            if ($response->failed()) {
                Log::error('Ollama request failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'model' => $model,
                    'base_url' => $baseUrl,
                ]);

                throw new \RuntimeException('Ollama request failed. Please ensure the Ollama server is running.');
            }

            $content = trim((string) $response->json('response', ''));

            if ($content === '') {
                throw new \RuntimeException('Ollama returned an empty response.');
            }

            return $content;
        } catch (Throwable $exception) {
            Log::error('Ollama service exception.', [
                'model' => $model,
                'base_url' => $baseUrl,
                'error' => $exception->getMessage(),
            ]);

            throw new \RuntimeException(
                'Local AI service is unavailable. Start Ollama and run: ollama run '.$model
            );
        }
    }
}
