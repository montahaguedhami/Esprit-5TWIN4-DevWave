<?php

namespace App\Services\Chatbot;

use App\Models\ChatbotDocumentChunk;

/**
 * Recherche documentaire (RAG) pour le chatbot.
 *
 * Recherche textuelle dans les chunks indexés. Retourne les passages les plus
 * pertinents avec leurs métadonnées (titre de document, section).
 *
 * Ceci n'est PAS une recherche vectorielle : c'est une recherche textuelle
 * full-text MySQL avec fallback LIKE. Aucun embedding n'est utilisé.
 */
class DocumentSearchService
{
    /**
     * Recherche les passages les plus pertinents pour une requête.
     *
     * @param  string  $query  texte de recherche
     * @param  int  $limit  nombre maximal de passages retournés
     * @return array<int, array{id: int, document_id: string, document_title: string, chunk_index: int, content: string, section: string|null, score: float}>
     */
    public function search(string $query, int $limit = 5): array
    {
        $query = trim($query);

        if ($query === '') {
            return [];
        }

        $chunks = ChatbotDocumentChunk::searchRelevant($query, $limit);

        return $chunks->map(fn (ChatbotDocumentChunk $chunk): array => [
            'id' => (int) $chunk->id,
            'document_id' => $chunk->document_id,
            'document_title' => $chunk->document_title,
            'chunk_index' => (int) $chunk->chunk_index,
            'content' => $chunk->content,
            'section' => $chunk->section,
            'score' => 1.0, // score textuel simple (pas de vectorisation)
        ])->values()->all();
    }

    /**
     * Vérifie si l'index contient au moins un document.
     */
    public function hasIndex(): bool
    {
        return ChatbotDocumentChunk::query()->exists();
    }

    /**
     * Nombre de documents indexés (distincts par document_id).
     */
    public function indexedDocumentCount(): int
    {
        return ChatbotDocumentChunk::query()->distinct('document_id')->count('document_id');
    }
}
