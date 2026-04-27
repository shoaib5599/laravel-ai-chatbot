<?php

namespace App\Services;

use App\Models\KnowledgeChunk;
use Illuminate\Support\Collection;

class RagService
{
    public function buildPromptWithContext(string $userMessage): string
    {
        $topK = (int) config('services.rag.top_k', 3);
        $maxContextChars = (int) config('services.rag.max_context_chars', 5000);

        $chunks = $this->retrieveRelevantChunks($userMessage, $topK);

        if ($chunks->isEmpty()) {
            return $userMessage;
        }

        $context = $this->formatContext($chunks, $maxContextChars);

        return <<<PROMPT
You are an assistant answering based on the provided context.
If the answer is not present in context, clearly say you do not have enough information.

Context:
{$context}

User question:
{$userMessage}
PROMPT;
    }

    private function retrieveRelevantChunks(string $query, int $topK): Collection
    {
        $tokens = $this->extractTokens($query);

        if (empty($tokens)) {
            return collect();
        }

        $candidateQuery = KnowledgeChunk::query();

        $candidateQuery->where(function ($builder) use ($tokens) {
            foreach ($tokens as $token) {
                $builder->orWhere('content', 'like', '%' . $token . '%');
            }
        });

        $candidates = $candidateQuery->limit(50)->get();

        $scored = $candidates
            ->map(function (KnowledgeChunk $chunk) use ($tokens) {
                $lower = mb_strtolower($chunk->content);
                $score = 0;

                foreach ($tokens as $token) {
                    $score += substr_count($lower, $token);
                }

                return [
                    'chunk' => $chunk,
                    'score' => $score,
                ];
            })
            ->filter(fn (array $item) => $item['score'] > 0)
            ->sortByDesc('score')
            ->take($topK)
            ->values()
            ->map(fn (array $item) => $item['chunk']);

        return $scored;
    }

    private function extractTokens(string $text): array
    {
        $stopWords = [
            'the', 'and', 'for', 'are', 'with', 'this', 'that', 'from', 'you', 'your',
            'what', 'when', 'where', 'which', 'how', 'why', 'can', 'please', 'about',
        ];

        $words = preg_split('/[^a-z0-9]+/i', mb_strtolower($text)) ?: [];

        $tokens = array_values(array_filter(array_unique($words), function (string $word) use ($stopWords) {
            return mb_strlen($word) >= 3 && ! in_array($word, $stopWords, true);
        }));

        return $tokens;
    }

    private function formatContext(Collection $chunks, int $maxChars): string
    {
        $context = '';

        foreach ($chunks as $index => $chunk) {
            $title = $chunk->title ?: 'Untitled';
            $entry = '[' . ($index + 1) . "] {$title}\n{$chunk->content}\n\n";

            if (mb_strlen($context . $entry) > $maxChars) {
                break;
            }

            $context .= $entry;
        }

        return trim($context);
    }
}
