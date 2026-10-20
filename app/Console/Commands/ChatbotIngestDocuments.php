<?php

namespace App\Console\Commands;

use App\Services\Chatbot\DocumentIngestionService;
use Illuminate\Console\Command;

class ChatbotIngestDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chatbot:ingest-documents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Indexe les documents autorisés sous docs/chatbot/ pour le RAG du chatbot.';

    /**
     * Execute the console command.
     */
    public function handle(DocumentIngestionService $ingestion): int
    {
        $this->info('Début de l\'ingestion documentaire du chatbot...');

        $result = $ingestion->ingest();

        $this->table(
            ['Métrique', 'Valeur'],
            [
                ['Passages indexés', $result['indexed']],
                ['Fichiers ignorés', count($result['ignored'])],
                ['Erreurs', count($result['errors'])],
            ]
        );

        if ($result['ignored'] !== []) {
            $this->warn('Fichiers ignorés :');
            foreach ($result['ignored'] as $file => $reason) {
                $this->warn("  - {$file} : {$reason}");
            }
        }

        if ($result['errors'] !== []) {
            $this->error('Erreurs :');
            foreach ($result['errors'] as $file => $error) {
                $this->error("  - {$file} : {$error}");
            }
        }

        $this->info('Ingestion terminée avec succès.');

        return self::SUCCESS;
    }
}
