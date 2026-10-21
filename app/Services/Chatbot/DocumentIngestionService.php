<?php

namespace App\Services\Chatbot;

use App\Models\ChatbotDocumentChunk;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Ingestion documentaire pour le RAG du chatbot.
 *
 * Lit uniquement les fichiers Markdown autorisés sous docs/chatbot/,
 * les découpe en passages, et les indexe dans la table
 * chatbot_document_chunks. Aucune donnée confidentielle n'est indexée.
 */
class DocumentIngestionService
{
    /**
     * Répertoire contenant les documents autorisés (relatif à la racine du projet).
     */
    private const DOCS_DIR = 'docs/chatbot';

    /**
     * Extensions de fichiers acceptées.
     */
    private const ALLOWED_EXTENSIONS = ['.md'];

    /**
     * Taille cible d'un passage en caractères.
     */
    private const CHUNK_TARGET_SIZE = 800;

    /**
     * Chevauchement entre deux passages consécutifs (caractères).
     */
    private const CHUNK_OVERLAP = 150;

    /**
     * Nombre maximal de passages par document (protection contre les fichiers trop volumineux).
     */
    private const MAX_CHUNKS_PER_DOCUMENT = 200;

    /**
     * @return array{indexed: int, ignored: array<string, string>, errors: array<string, string>}
     */
    public function ingest(): array
    {
        $indexed = 0;
        $ignored = [];
        $errors = [];

        $docsDir = base_path(self::DOCS_DIR);

        if (! is_dir($docsDir)) {
            Log::warning('Document ingestion : répertoire introuvable.', ['dir' => $docsDir]);
            return ['indexed' => 0, 'ignored' => [], 'errors' => ['Répertoire ' . self::DOCS_DIR . ' introuvable.']];
        }

        $files = scandir($docsDir);
        sort($files);

        foreach ($files as $file) {
            $path = $docsDir . DIRECTORY_SEPARATOR . $file;

            if (! is_file($path)) {
                continue;
            }

            $extension = Str::afterLast($file, '.');
            $extension = $extension !== '' ? '.' . $extension : '';

            if (! in_array(strtolower($extension), self::ALLOWED_EXTENSIONS, true)) {
                $ignored[$file] = 'Extension non autorisée : ' . $extension;
                continue;
            }

            // Sécurité : ne jamais indexer de fichier sensible, même par erreur
            if ($this->isForbiddenFile($file)) {
                $ignored[$file] = 'Fichier exclu par politique de sécurité.';
                continue;
            }

            try {
                $content = file_get_contents($path);
                if ($content === false) {
                    $errors[$file] = 'Impossible de lire le fichier.';
                    continue;
                }

                $documentId = pathinfo($file, PATHINFO_FILENAME);
                $documentTitle = $this->extractTitle($content) ?? $documentId;

                // Réindexation propre : supprimer les anciens passages de ce document
                ChatbotDocumentChunk::purgeDocument($documentId);

                $chunks = $this->chunkContent($content, $documentTitle);
                $maxChunks = min(count($chunks), self::MAX_CHUNKS_PER_DOCUMENT);

                for ($i = 0; $i < $maxChunks; $i++) {
                    ChatbotDocumentChunk::create([
                        'document_id' => $documentId,
                        'document_title' => $documentTitle,
                        'chunk_index' => $i,
                        'content' => $chunks[$i],
                        'section' => $this->detectSection($chunks[$i]),
                    ]);
                    $indexed++;
                }

                Log::info('Document indexé.', ['document' => $documentId, 'chunks' => $maxChunks]);
            } catch (\Throwable $e) {
                Log::error('Erreur lors de l\'indexation de ' . $file, ['error' => $e->getMessage()]);
                $errors[$file] = $e->getMessage();
            }
        }

        Log::info('Ingestion terminée.', ['indexed' => $indexed, 'ignored' => count($ignored), 'errors' => count($errors)]);

        return ['indexed' => $indexed, 'ignored' => $ignored, 'errors' => $errors];
    }

    /**
     * Détermine si un fichier est interdit à l'indexation.
     */
    private function isForbiddenFile(string $fileName): bool
    {
        $forbidden = [
            '.env', '.env.example', '.env.backup', '.env.production',
            'composer.lock', 'package-lock.json',
            'sqlite', '.sqlite',
        ];

        $lower = strtolower($fileName);

        foreach ($forbidden as $forbiddenPattern) {
            if (str_contains($lower, $forbiddenPattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extrait le titre du document depuis la première ligne H1 Markdown.
     */
    private function extractTitle(string $content): ?string
    {
        if (preg_match('/^#\s+(.+)$/m', $content, $match)) {
            return trim($match[1]);
        }

        return null;
    }

    /**
     * Découpe un document en passages de taille cible avec chevauchement.
     *
     * @return list<string>
     */
    private function chunkContent(string $content, string $documentTitle): array
    {
        $content = trim($content);

        if ($content === '') {
            return [];
        }

        // Découpe par paragraphes d'abord pour respecter la structure
        $paragraphs = preg_split('/\n\s*\n/', $content);
        $paragraphs = array_map('trim', $paragraphs);
        $paragraphs = array_filter($paragraphs, fn ($p) => $p !== '');

        $chunks = [];
        $current = '';

        foreach ($paragraphs as $paragraph) {
            $separator = $current === '' ? '' : "\n\n";
            $candidate = $current . $separator . $paragraph;

            if (mb_strlen($candidate) <= self::CHUNK_TARGET_SIZE) {
                $current = $candidate;
            } else {
                if ($current !== '') {
                    $chunks[] = $current;
                }

                // Si le paragraphe seul dépasse la cible, le couper avec chevauchement
                if (mb_strlen($paragraph) > self::CHUNK_TARGET_SIZE) {
                    $subChunks = $this->splitWithOverlap($paragraph);
                    foreach ($subChunks as $index => $subChunk) {
                        if ($index === count($subChunks) - 1) {
                            $current = $subChunk;
                        } else {
                            $chunks[] = $subChunk;
                        }
                    }
                } else {
                    $current = $paragraph;
                }
            }
        }

        if ($current !== '') {
            $chunks[] = $current;
        }

        return array_values($chunks);
    }

    /**
     * Coupe un texte long en segments avec chevauchement.
     *
     * @return list<string>
     */
    private function splitWithOverlap(string $text): array
    {
        $segments = [];
        $length = mb_strlen($text);
        $target = self::CHUNK_TARGET_SIZE;
        $overlap = self::CHUNK_OVERLAP;
        $step = $target - $overlap;

        $start = 0;
        while ($start < $length) {
            $end = min($start + $target, $length);
            $segments[] = mb_substr($text, $start, $end - $start);

            if ($end >= $length) {
                break;
            }

            $start += $step;
        }

        return $segments;
    }

    /**
     * Détecte une section de titre (H1/H2/H3) au début d'un passage.
     */
    private function detectSection(string $chunk): ?string
    {
        if (preg_match('/^#{1,3}\s+(.+)$/m', $chunk, $match)) {
            return trim($match[1]);
        }

        return null;
    }
}
