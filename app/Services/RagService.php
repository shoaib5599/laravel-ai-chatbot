<?php

namespace App\Services;

use App\Models\KnowledgeChunk;
use Illuminate\Support\Collection;

class RagService
{
    public const NO_CONTEXT_SENTINEL = '__RAG_NO_CONTEXT__';

    public const NO_CONTEXT_MESSAGE = 'I do not have enough information in the provided knowledge base.';

    public function buildPromptWithContext(string $userMessage): string
    {
        $topK = (int) config('services.rag.top_k', 3);
        $maxContextChars = (int) config('services.rag.max_context_chars', 5000);
        $minScore = (int) config('services.rag.min_relevance_score', 2);
        $minTokenCoverage = (float) config('services.rag.min_token_coverage', 0.6);
        $tokens = $this->extractTokens($userMessage);

        [$chunks, $bestScore] = $this->retrieveRelevantChunks($userMessage, $topK);
        $tokenCoverage = $this->calculateTokenCoverage($chunks, $tokens);

        if ($chunks->isEmpty() || $bestScore < $minScore || $tokenCoverage < $minTokenCoverage) {
            return self::NO_CONTEXT_SENTINEL;
        }

        $context = $this->formatContext($chunks, $maxContextChars);

        return <<<PROMPT
You are a strict retrieval-grounded assistant.
Answer ONLY using facts explicitly present in the provided context.
Do NOT infer, guess, or fill missing details from general knowledge.
If the exact answer is not stated in context, reply with exactly:
"I do not have enough information in the provided knowledge base."

When you provide an answer, keep it concise and include one short supporting quote from the context.

Context:
{$context}

User question:
{$userMessage}
PROMPT;
    }

    /**
     * @return array{0: Collection<int, KnowledgeChunk>, 1: int}
     */
    private function retrieveRelevantChunks(string $query, int $topK): array
    {
        $tokens = $this->extractTokens($query);

        if (empty($tokens)) {
            return [collect(), 0];
        }

        $candidateQuery = KnowledgeChunk::query();

        $candidateQuery->where(function ($builder) use ($tokens) {
            foreach ($tokens as $token) {
                $builder->orWhere('content', 'like', '%' . $token . '%');
            }
        });

        $candidates = $candidateQuery->limit(50)->get();

        $scoredItems = $candidates
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
            ->values();

        $bestScore = (int) ($scoredItems->first()['score'] ?? 0);
        $chunks = $scoredItems->map(fn (array $item) => $item['chunk']);

        return [$chunks, $bestScore];
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

    private function calculateTokenCoverage(Collection $chunks, array $tokens): float
    {
        if (empty($tokens)) {
            return 0.0;
        }

        $combinedContext = mb_strtolower($chunks->pluck('content')->implode(' '));
        $matched = 0;

        foreach ($tokens as $token) {
            if (str_contains($combinedContext, $token)) {
                $matched++;
            }
        }

        return $matched / count($tokens);
    }
}
