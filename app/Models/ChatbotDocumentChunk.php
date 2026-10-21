<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un passage (chunk) d'un document documentaire autorisé pour le RAG du chatbot.
 *
 * Aucune donnée privée (citoyen, paiement, demande) n'est jamais indexée ici.
 * Seule la documentation publique du module Projets et Financements est admise.
 */
class ChatbotDocumentChunk extends Model
{
    use HasFactory;

    protected $table = 'chatbot_document_chunks';

    protected $fillable = [
        'document_id',
        'document_title',
        'chunk_index',
        'content',
        'section',
    ];

    protected $casts = [
        'chunk_index' => 'integer',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(self::class, 'document_id');
    }

    /**
     * Recherche les passages pertinents pour une requête texte.
     *
     * Utilise la recherche full-text MySQL si disponible, sinon une recherche
     * par LIKE sur les mots-clés (au moins un terme doit matcher). Retourne au
     * maximum $limit passages.
     *
     * @param  string  $query  texte de recherche
     * @param  int  $limit  nombre maximal de passages retournés
     * @return \Illuminate\Database\Eloquent\Collection<int, static>
     */
    public static function searchRelevant(string $query, int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        $query = trim((string) $query);

        if ($query === '') {
            return collect();
        }

        // Recherche full-text MySQL quand le moteur le supporte
        try {
            $results = self::query()
                ->whereRaw('MATCH(content) AGAINST(? IN BOOLEAN MODE)', [$query])
                ->orderBy('document_id')
                ->orderBy('chunk_index')
                ->limit($limit)
                ->get();

            if ($results->isNotEmpty()) {
                return $results;
            }
        } catch (\Throwable) {
            // Full-text non disponible (ex: SQLite) ou index absent : fallback LIKE
        }

        // Fallback : recherche par LIKE sur chaque terme (au moins un terme doit matcher)
        // On reconstruit un builder vierge pour ne pas conserver la clause whereRaw échouée.
        $builder = self::query()->orderBy('document_id')->orderBy('chunk_index');

        $terms = array_filter(preg_split('/\s+/', mb_strtolower($query)));
        if ($terms !== []) {
            $builder->where(function ($q) use ($terms) {
                $first = true;
                foreach ($terms as $term) {
                    if ($first) {
                        $q->where('content', 'LIKE', "%{$term}%");
                        $first = false;
                    } else {
                        $q->orWhere('content', 'LIKE', "%{$term}%");
                    }
                }
            });
        }

        return $builder->limit($limit)->get();
    }

    /**
     * Supprime tous les chunks d'un document donné (pour réindexation propre).
     */
    public static function purgeDocument(string $documentId): int
    {
        return self::query()->where('document_id', $documentId)->delete();
    }

    /**
     * Vérifie si un document est déjà indexé.
     */
    public static function isIndexed(string $documentId): bool
    {
        return self::query()->where('document_id', $documentId)->exists();
    }
}
