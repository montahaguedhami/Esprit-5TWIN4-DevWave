<?php

namespace Tests\Feature;

use App\Models\ChatbotDocumentChunk;
use App\Services\Chatbot\DocumentIngestionService;
use App\Services\Chatbot\DocumentSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests RAG : ingestion, découpage, recherche, références réelles.
 *
 * Aucun appel externe n'est effectué.
 */
class ChatbotRagTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        // Indexer les documents pour les tests de recherche
        app(DocumentIngestionService::class)->ingest();
    }

    public function test_ingestion_indexe_documents_autorises(): void
    {
        $service = app(DocumentIngestionService::class);
        $result = $service->ingest();

        $this->assertGreaterThan(0, $result['indexed']);
        $this->assertEquals(0, count($result['errors']));
        $this->assertDatabaseHas('chatbot_document_chunks', ['document_id' => 'guide-projets']);
    }

    public function test_recherche_retourne_passages_pertinents(): void
    {
        // Ingestion dans le test pour garantir la disponibilité des données
        app(DocumentIngestionService::class)->ingest();

        $search = app(DocumentSearchService::class);
        $results = $search->search('montant', 5);

        $this->assertNotEmpty($results, 'La recherche doit trouver des passages contenant "montant".');
        $this->assertArrayHasKey('document_title', $results[0]);
        $this->assertArrayHasKey('content', $results[0]);
        $this->assertArrayHasKey('document_id', $results[0]);
    }

    public function test_aucune_citation_inventee_sans_source(): void
    {
        $search = app(DocumentSearchService::class);
        // Recherche sur un terme qui ne correspond à aucun document
        $results = $search->search('xyzwqwerty123456789 nonexistent', 5);

        $this->assertEmpty($results);
    }

    public function test_reindexation_sans_duplication(): void
    {
        $service = app(DocumentIngestionService::class);
        $service->ingest();
        $count1 = ChatbotDocumentChunk::query()->count();

        $service->ingest();
        $count2 = ChatbotDocumentChunk::query()->count();

        $this->assertEquals($count1, $count2, 'La réindexation ne doit pas dupliquer les passages.');
    }

    public function test_aucun_fichier_secret_dans_index(): void
    {
        $service = app(DocumentIngestionService::class);
        $service->ingest();

        $secrets = ChatbotDocumentChunk::query()
            ->where(function ($q) {
                $q->where('content', 'like', '%AIza%')
                    ->orWhere('content', 'like', '%GEMINI_API_KEY%')
                    ->orWhere('content', 'like', '%.env%');
            })
            ->count();

        $this->assertEquals(0, $secrets, 'Aucun secret ne doit être indexé.');
    }

    public function test_document_titre_conserve(): void
    {
        $service = app(DocumentIngestionService::class);
        $service->ingest();

        $chunks = ChatbotDocumentChunk::query()->where('document_id', 'guide-projets')->get();
        $this->assertNotEmpty($chunks);
        $this->assertNotEmpty($chunks->first()->document_title);
    }

    public function test_behavior_quand_aucune_source_pertinente(): void
    {
        // Terme qui ne correspond à aucun document indexé
        $search = app(DocumentSearchService::class);
        $results = $search->search('xyzwqwerty123456789 inexistant', 5);
        $this->assertEmpty($results);
    }
}
