<?php

namespace App\Console\Commands;

use App\Models\KnowledgeChunk;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class IngestKnowledgeCommand extends Command
{
    protected $signature = 'rag:ingest {path=storage/app/knowledge}';

    protected $description = 'Ingest .txt and .md files into knowledge chunks for RAG.';

    public function handle(): int
    {
        $relativePath = (string) $this->argument('path');
        $path = base_path($relativePath);

        if (! File::exists($path)) {
            $this->error("Path not found: {$path}");

            return self::FAILURE;
        }

        $files = collect(File::allFiles($path))
            ->filter(fn ($file) => in_array($file->getExtension(), ['txt', 'md'], true))
            ->values();

        if ($files->isEmpty()) {
            $this->warn('No .txt or .md files found to ingest.');

            return self::SUCCESS;
        }

        $inserted = 0;

        foreach ($files as $file) {
            $sourcePath = Str::after($file->getPathname(), base_path() . DIRECTORY_SEPARATOR);
            $raw = trim(File::get($file->getPathname()));

            if ($raw === '') {
                continue;
            }

            KnowledgeChunk::query()->where('source_path', $sourcePath)->delete();

            $chunks = $this->chunkText($raw, 1200, 200);
            $title = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            foreach ($chunks as $chunk) {
                KnowledgeChunk::query()->create([
                    'title' => $title,
                    'content' => $chunk,
                    'source_path' => $sourcePath,
                ]);

                $inserted++;
            }
        }

        $this->info("RAG ingest completed. Inserted {$inserted} chunks.");

        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    private function chunkText(string $text, int $size, int $overlap): array
    {
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;
        $text = trim($text);

        if ($text === '') {
            return [];
        }

        $chunks = [];
        $length = mb_strlen($text);
        $start = 0;

        while ($start < $length) {
            $chunk = mb_substr($text, $start, $size);
            $chunks[] = trim($chunk);

            if ($start + $size >= $length) {
                break;
            }

            $start += ($size - $overlap);
        }

        return $chunks;
    }
}
