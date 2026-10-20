<?php

namespace App\Services;

use App\Models\Infrastructure;
use App\Models\Intervention;
use App\Models\Technicien;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Recommandation du technicien pour une intervention.
 *
 * 1. Calcul déterministe : exclusion (congé, conflit de date) puis score (spécialité, expérience, charge).
 * 2. Le modèle Ollama choisit parmi les meilleurs candidats et justifie son choix en français.
 * 3. Réponse du modèle invalide ou indisponible : le meilleur score est retenu (« calcul de secours »).
 */
class TechnicianRecommendationService
{
    public const SOURCE_IA = 'ia';

    public const SOURCE_SECOURS = 'secours';

    public const SOURCE_AUCUN = 'aucun';

    /** Nombre de candidats soumis au modèle. */
    private const CANDIDATS_IA = 4;

    public function __construct(
        private InterventionPlanningService $planning,
        private OllamaClient $ollama,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function recommander(?Infrastructure $infrastructure, Carbon $date, string $description, ?int $interventionIgnoree = null): array
    {
        $specialite = $this->specialiteAttendue($infrastructure, $description);
        $classement = $this->classement($infrastructure, $date, $specialite, $interventionIgnoree);

        if ($classement->isEmpty()) {
            return [
                'source' => self::SOURCE_AUCUN,
                'specialite_attendue' => $specialite,
                'message' => 'Aucun technicien disponible le '.$date->format('d/m/Y').' (congés ou interventions déjà planifiées).',
                'technicien' => null,
                'justification' => null,
                'vigilance' => null,
                'classement' => [],
            ];
        }

        $candidats = $classement->take(self::CANDIDATS_IA)->values();
        $reponse = $this->ollama->json(
            $this->prompt($infrastructure, $date, $description, $specialite, $candidats),
            (int) config('maintenance.ia_timeout', 30)
        );

        $choisi = $reponse ? $candidats->firstWhere('id', (int) ($reponse['technicien_id'] ?? 0)) : null;
        $justification = is_string($reponse['justification'] ?? null) ? trim($reponse['justification']) : '';

        if ($choisi && $justification !== '') {
            $source = self::SOURCE_IA;
            $vigilance = is_string($reponse['vigilance'] ?? null) && trim($reponse['vigilance']) !== '' ? trim($reponse['vigilance']) : null;
        } else {
            $source = self::SOURCE_SECOURS;
            $choisi = $candidats->first();
            $justification = $choisi['nom'].' obtient le meilleur score : '.implode(', ', $choisi['raisons']).'.';
            $vigilance = null;
        }

        // Vérification côté serveur (indépendante du modèle) : suggestion hors de la spécialité attendue
        if ($specialite !== null && $choisi['specialite'] !== $specialite) {
            $alerte = $classement->contains('specialite', $specialite)
                ? "Un technicien « {$specialite} » est disponible : vérifiez ce choix hors spécialité."
                : "Aucun technicien « {$specialite} » disponible le {$date->format('d/m/Y')} : suggestion hors spécialité.";
            $vigilance = $vigilance ? $alerte.' '.$vigilance : $alerte;
        }

        return [
            'source' => $source,
            'specialite_attendue' => $specialite,
            'message' => null,
            'technicien' => Arr::only($choisi, ['id', 'nom', 'specialite']),
            'justification' => Str::limit($justification, 600),
            'vigilance' => $vigilance ? Str::limit($vigilance, 300) : null,
            'classement' => $classement->take(3)->values()->all(),
        ];
    }

    /**
     * Spécialité attendue : par le type d'infrastructure, sinon par les mots-clés de la description.
     */
    public function specialiteAttendue(?Infrastructure $infrastructure, string $description): ?string
    {
        if ($infrastructure?->type) {
            $specialite = config('maintenance.specialite_par_type.'.$infrastructure->type->value);
            if ($specialite) {
                return $specialite;
            }
        }

        $texte = mb_strtolower($description);
        foreach (config('maintenance.mots_cles_specialite', []) as $specialite => $motsCles) {
            foreach ($motsCles as $motCle) {
                if (str_contains($texte, $motCle)) {
                    return $specialite;
                }
            }
        }

        return null;
    }

    /**
     * Techniciens éligibles triés par score décroissant.
     *
     * @return Collection<int, array{id: int, nom: string, specialite: string, disponibilite: string, charge: int, experience_equipement: int, experience_zone: int, score: int, raisons: list<string>}>
     */
    public function classement(?Infrastructure $infrastructure, Carbon $date, ?string $specialite, ?int $interventionIgnoree = null): Collection
    {
        $fenetre = (int) config('maintenance.fenetre_charge_jours', 30);
        $terminees = Intervention::query()
            ->with('infrastructure:id,zone_id')
            ->where('statut', 'Terminée')
            ->whereNotNull('infrastructure_id')
            ->get(['id', 'technicien_id', 'infrastructure_id']);

        return Technicien::query()
            ->where('disponibilite', '!=', 'En congé')
            ->withCount(['interventions as charge' => fn ($query) => $query
                ->whereIn('statut', InterventionPlanningService::STATUTS_ACTIFS)
                ->whereDate('date', '>=', today()->toDateString())
                ->whereDate('date', '<=', today()->addDays($fenetre)->toDateString())
                ->when($interventionIgnoree, fn ($q) => $q->whereKeyNot($interventionIgnoree))])
            ->get()
            ->reject(fn (Technicien $t) => $this->planning->conflitPour($t->id, $date, $interventionIgnoree) !== null)
            ->map(function (Technicien $technicien) use ($infrastructure, $specialite, $terminees) {
                $siennes = $terminees->where('technicien_id', $technicien->id);
                $surEquipement = $infrastructure ? $siennes->where('infrastructure_id', $infrastructure->id)->count() : 0;
                $dansZone = $infrastructure?->zone_id
                    ? $siennes->filter(fn (Intervention $i) => $i->infrastructure?->zone_id === $infrastructure->zone_id)->count()
                    : 0;
                $bonneSpecialite = $specialite !== null && $technicien->specialite === $specialite;

                $raisons = [];
                $raisons[] = $bonneSpecialite ? 'spécialité '.$technicien->specialite : 'autre spécialité ('.$technicien->specialite.')';
                if ($surEquipement > 0) {
                    $raisons[] = $surEquipement.' intervention(s) déjà réalisée(s) sur cet équipement';
                }
                if ($dansZone > 0) {
                    $raisons[] = $dansZone.' intervention(s) dans cette zone';
                }
                $raisons[] = $technicien->charge.' intervention(s) prévue(s) sur '.config('maintenance.fenetre_charge_jours', 30).' jours';

                return [
                    'id' => $technicien->id,
                    'nom' => $technicien->nom,
                    'specialite' => $technicien->specialite,
                    'disponibilite' => $technicien->disponibilite,
                    'charge' => $technicien->charge,
                    'experience_equipement' => $surEquipement,
                    'experience_zone' => $dansZone,
                    'score' => ($bonneSpecialite ? 50 : 0)
                        + min($surEquipement, 3) * 8
                        + min($dansZone, 5) * 3
                        + ($technicien->disponibilite === 'Disponible' ? 5 : 0)
                        - $technicien->charge * 4,
                    'raisons' => $raisons,
                ];
            })
            ->sortBy([['score', 'desc'], ['nom', 'asc']])
            ->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $candidats
     */
    private function prompt(?Infrastructure $infrastructure, Carbon $date, string $description, ?string $specialite, Collection $candidats): string
    {
        $equipement = $infrastructure
            ? sprintf('%s « %s » (zone %s, criticité %s)',
                $infrastructure->type?->value ?? 'équipement',
                $infrastructure->nom,
                $infrastructure->zone?->nom ?? 'inconnue',
                $infrastructure->niveau_criticite?->value ?? 'inconnue')
            : 'aucun équipement précisé';

        $lignes = $candidats->map(fn (array $c) => sprintf(
            '- id %d : %s, spécialité %s, %s, %d intervention(s) prévue(s), %d sur cet équipement, %d dans la zone, score indicatif %d',
            $c['id'], $c['nom'], $c['specialite'], mb_strtolower($c['disponibilite']),
            $c['charge'], $c['experience_equipement'], $c['experience_zone'], $c['score']
        ))->implode("\n");

        return <<<PROMPT
        Tu es l'assistant du gestionnaire de maintenance d'un réseau d'eau potable en Tunisie.
        Choisis le technicien le plus adapté pour l'intervention suivante, uniquement parmi les candidats listés.

        Intervention prévue le {$date->format('d/m/Y')}
        Équipement : {$equipement}
        Spécialité attendue : {$this->texte($specialite)}
        Description : {$this->texte($description)}

        Candidats disponibles ce jour-là (aucun conflit de planning) :
        {$lignes}

        {$this->noteSpecialite($specialite, $candidats)}
        Critères : privilégie la bonne spécialité, puis l'expérience sur l'équipement ou la zone, puis la charge la plus faible.
        Réponds uniquement en JSON, en français :
        {"technicien_id": <id d'un candidat>, "justification": "<2 phrases maximum>", "vigilance": "<un point d'attention ou chaîne vide>"}
        PROMPT;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $candidats
     */
    private function noteSpecialite(?string $specialite, Collection $candidats): string
    {
        if ($specialite === null || $candidats->contains('specialite', $specialite)) {
            return '';
        }

        return "Attention : aucun candidat n'a la spécialité {$specialite} ; choisis le plus polyvalent et signale-le dans « vigilance ».";
    }

    private function texte(?string $valeur): string
    {
        $valeur = trim((string) $valeur);

        return $valeur === '' ? 'non précisée' : Str::limit($valeur, 400);
    }
}
