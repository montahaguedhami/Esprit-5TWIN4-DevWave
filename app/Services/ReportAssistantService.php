<?php

namespace App\Services;

use App\Enums\InfrastructureEtat;
use App\Models\Intervention;
use Illuminate\Support\Str;

/**
 * Rédaction assistée du rapport d'intervention : transforme les notes rapides du technicien
 * en rapport structuré (résultat, travaux, cause, observations, pièces, nouvel état).
 *
 * Le résultat n'est qu'une proposition : le technicien relit et valide lui-même le formulaire.
 * Toute valeur renvoyée par le modèle est contrôlée ; sans modèle, un brouillon simple est produit.
 */
class ReportAssistantService
{
    public const SOURCE_IA = 'ia';

    public const SOURCE_SECOURS = 'secours';

    public const RESULTATS = ['resolved', 'partial', 'failed', 'followup'];

    /** Mots-clés du calcul de secours, du plus grave au plus favorable. */
    private const MOTS_CLES_RESULTAT = [
        'failed' => ['impossible', 'échec', 'echec', 'pièce manquante', 'piece manquante', 'à l\'arrêt', 'hors service', 'pas pu'],
        'followup' => ['revoir', 'repasser', 'à surveiller', 'a surveiller', 'prévoir', 'prevoir', 'contrôler dans', 'nouvelle visite'],
        'partial' => ['provisoire', 'partiel', 'dégradé', 'degrade', 'temporaire', 'en attendant'],
    ];

    public function __construct(private OllamaClient $ollama)
    {
    }

    /**
     * @return array{source: string, status: string, work_performed: string, cause: string, observations: string, nouvel_etat: ?string, parts: list<array{name: string, qty: int}>}
     */
    public function rediger(Intervention $intervention, string $notes): array
    {
        $notes = trim($notes);
        $reponse = $this->ollama->json($this->prompt($intervention, $notes), (int) config('maintenance.ia_timeout', 30));

        $travaux = $this->texte($reponse['work_performed'] ?? null, 3000);

        if ($reponse === null || $travaux === '') {
            return $this->brouillon($notes);
        }

        // En cas de désaccord, on garde le résultat le plus prudent : les notes signalant un suivi,
        // une réparation provisoire ou un échec ne peuvent pas être clôturées comme « résolu »
        $parMotsCles = $this->resultatParMotsCles($notes);
        $statut = in_array($reponse['status'] ?? null, self::RESULTATS, true) ? $reponse['status'] : $parMotsCles;

        // Le modèle conclut à un succès alors que les notes décrivent un échec : il a mal compris les notes,
        // son texte n'est pas fiable (risque d'affirmer une remise en service qui n'a pas eu lieu)
        if ($statut === 'resolved' && $parMotsCles === 'failed') {
            return $this->brouillon($notes);
        }

        // Échec décrit dans les notes (pièce manquante, ouvrage à l'arrêt) : c'est un fait, il prime
        if (($statut === 'resolved' && $parMotsCles !== 'resolved') || $parMotsCles === 'failed') {
            $statut = $parMotsCles;
        }

        $etats = array_map(fn (InfrastructureEtat $e) => $e->value, InfrastructureEtat::cases());
        $nouvelEtat = in_array($reponse['nouvel_etat'] ?? null, $etats, true) ? $reponse['nouvel_etat'] : null;

        // Un ouvrage en échec ou en réparation partielle n'est pas « En service » : l'état automatique s'applique
        if ($nouvelEtat === InfrastructureEtat::EnService->value && in_array($statut, ['failed', 'partial'], true)) {
            $nouvelEtat = null;
        }

        // Suite à donner présente dans les notes mais oubliée par le modèle : reprise dans les observations
        $observations = $this->texte($reponse['observations'] ?? null, 1000);
        if ($observations === '' && in_array($statut, ['followup', 'partial'], true)) {
            $observations = $this->suiteADonner($notes);
        }

        return [
            'source' => self::SOURCE_IA,
            'status' => $statut,
            'work_performed' => $travaux,
            'cause' => Str::ucfirst($this->texte($reponse['cause'] ?? null, 1000)),
            'observations' => $observations,
            'nouvel_etat' => $nouvelEtat,
            'parts' => $this->pieces($reponse['parts'] ?? []),
        ];
    }

    /**
     * Brouillon sans modèle : notes reprises telles quelles, résultat deviné par mots-clés.
     *
     * @return array{source: string, status: string, work_performed: string, cause: string, observations: string, nouvel_etat: ?string, parts: list<array{name: string, qty: int}>}
     */
    public function brouillon(string $notes): array
    {
        $texte = Str::ucfirst(trim($notes));
        if ($texte !== '' && ! preg_match('/[.!?]$/u', $texte)) {
            $texte .= '.';
        }

        return [
            'source' => self::SOURCE_SECOURS,
            'status' => $this->resultatParMotsCles($notes),
            'work_performed' => Str::limit($texte, 3000, ''),
            'cause' => '',
            'observations' => '',
            'nouvel_etat' => null,
            'parts' => [],
        ];
    }

    public function resultatParMotsCles(string $notes): string
    {
        $texte = mb_strtolower($notes);

        foreach (self::MOTS_CLES_RESULTAT as $resultat => $motsCles) {
            foreach ($motsCles as $motCle) {
                if (str_contains($texte, $motCle)) {
                    return $resultat;
                }
            }
        }

        return 'resolved';
    }

    /**
     * Fragment des notes qui annonce une suite (« revoir dans 1 semaine »), mis en forme pour les observations.
     */
    public function suiteADonner(string $notes): string
    {
        $motsCles = array_merge(self::MOTS_CLES_RESULTAT['followup'], self::MOTS_CLES_RESULTAT['partial']);

        foreach (preg_split('/[.;,\n]+/u', $notes) as $fragment) {
            $fragment = trim($fragment);
            foreach ($motsCles as $motCle) {
                if ($fragment !== '' && str_contains(mb_strtolower($fragment), $motCle)) {
                    return 'À prévoir : '.$fragment.'.';
                }
            }
        }

        return '';
    }

    private function prompt(Intervention $intervention, string $notes): string
    {
        $infrastructure = $intervention->infrastructure;
        $equipement = $infrastructure
            ? sprintf('%s « %s »', $infrastructure->type?->value ?? 'équipement', $infrastructure->nom)
            : 'non précisé';
        $etats = implode(', ', array_map(fn (InfrastructureEtat $e) => '"'.$e->value.'"', InfrastructureEtat::cases()));
        $mission = Str::limit(strtok((string) $intervention->description, "\n") ?: '', 300);

        return <<<PROMPT
        Tu es l'assistant de rédaction des techniciens de maintenance d'un réseau d'eau potable en Tunisie.
        Transforme les notes rapides du technicien en rapport d'intervention clair et professionnel, en français.

        Intervention #{$intervention->id} ({$intervention->type}) — équipement : {$equipement}
        Mission prévue : {$mission}

        Notes du technicien :
        """{$notes}"""

        Règles :
        - N'invente aucun fait, aucune mesure et aucune pièce absents des notes.
        - La mission prévue n'est qu'un contexte : ne la décris jamais comme un travail réalisé. Seules les notes disent ce qui a été fait.
        - Si les notes décrivent un échec (impossible, pièce manquante, réseau coupé), ne prétends jamais que le problème est réglé.
        - Reformule : ne recopie jamais les notes mot pour mot, écris des phrases complètes, corrige l'orthographe et rétablis les accents.
        - "work_performed" : travaux réalisés, phrases complètes, 2 à 5 phrases.
        - "cause" : cause du problème si les notes l'indiquent, sinon chaîne vide.
        - "observations" : remarques et suites à donner si les notes en contiennent, sinon chaîne vide.
        - "status" : "resolved" (problème résolu), "partial" (réparation provisoire ou fonctionnement dégradé), "failed" (échec, pièce manquante, ouvrage à l'arrêt) ou "followup" (nouvelle visite nécessaire).
        - "nouvel_etat" : un de {$etats}, ou null si les notes ne permettent pas de le dire.
        - "parts" : pièces NEUVES posées citées dans les notes, avec la quantité (1 si non précisée), sinon liste vide. Ne liste pas la pièce défectueuse retirée.
        - Toute visite ou vérification future ("revoir", "à surveiller", "dans 1 semaine") va dans "observations" et donne le statut "followup".

        Exemple :
        Notes : "vanne V3 grippée, graissage + 1 joint plat DN80 neuf, fonctionne, à revoir dans 15 j"
        Réponse : {"status": "followup", "work_performed": "Graissage de la vanne V3 et remplacement de son joint plat. La vanne fonctionne de nouveau.", "cause": "Vanne V3 grippée.", "observations": "Prévoir une nouvelle vérification de la vanne V3 dans 15 jours.", "nouvel_etat": "En service", "parts": [{"name": "Joint plat DN80", "qty": 1}]}

        Réponds uniquement en JSON :
        {"status": "...", "work_performed": "...", "cause": "...", "observations": "...", "nouvel_etat": "..." ou null, "parts": [{"name": "...", "qty": 1}]}
        PROMPT;
    }

    private function texte(mixed $valeur, int $max): string
    {
        return is_string($valeur) ? Str::limit(trim($valeur), $max, '') : '';
    }

    /**
     * @return list<array{name: string, qty: int}>
     */
    private function pieces(mixed $pieces): array
    {
        if (! is_array($pieces)) {
            return [];
        }

        return collect($pieces)
            ->filter(fn ($p) => is_array($p) && is_string($p['name'] ?? null) && trim($p['name']) !== '')
            ->map(fn (array $p) => [
                'name' => Str::limit(trim($p['name']), 100, ''),
                'qty' => max(1, min(99, (int) ($p['qty'] ?? 1))),
            ])
            // Pièce défectueuse citée par erreur (« HS ») et doublons écartés
            ->reject(fn (array $p) => preg_match('/(\bhs\b|défectueu)/iu', $p['name']) === 1)
            ->unique(fn (array $p) => mb_strtolower($p['name']))
            ->take(10)
            ->values()
            ->all();
    }
}
