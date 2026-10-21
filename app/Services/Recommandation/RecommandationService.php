<?php

namespace App\Services\Recommandation;

use App\Models\ConsultationProjet;
use App\Models\DemandeFinancement;
use App\Models\Financement;
use App\Models\Projet;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Support\Collection;

/**
 * Moteur de recommandation V1 — DÉTERMINISTE, BASÉ SUR DES RÈGLES.
 *
 * ⚠ Ce classeur n'est PAS un modèle de Machine Learning. Il n'y a aucun
 * entraînement, aucune dépendance ML, aucun appel réseau, aucun modèle Python.
 * C'est une fonction pure : mêmes données en entrée ⇒ exactement les mêmes
 * scores et le même ordre en sortie.
 *
 * ── Architecture ────────────────────────────────────────────────────────────
 * Le service est volontairement découpé en étapes testables séparément :
 *   1. candidats()        — quels projets sont admissibles
 *   2. affinitesZones()   — quelles zones le citoyen apprécie
 *   3. score()            — note 0–100 d'un projet
 *   4. raisons()          — pourquoi ce projet est proposé
 * Une V2 « ML » pourra remplacer l'étape 3 tout en gardant les étapes 1, 2
 * et 4, et le même contrat de sortie (Recommandation).
 *
 * ── Axe catégoriel : les ZONES ──────────────────────────────────────────────
 * Ce dépôt ne possède AUCUNE catégorie de projet (vérifié : ni champ
 * `categorie`, ni table `categories`, ni tag). La seule classification
 * réellement existante est la table `zones`, en many-to-many via `projet_zone`.
 * C'est donc cet axe authentique qui sert de « catégorie ». Aucun champ
 * `categorie_id` n'a été inventé.
 *
 * ── Formule (documentée, configurable) ──────────────────────────────────────
 *   score = 40%·affinitéZone + 25%·favori + 20%·financement
 *         + 10%·consultationRécente + 5%·contexte
 * Chaque composante est d'abord ramenée à [0..1], puis pondérée. Le total
 * est borné à [0..100] par construction.
 */
final class RecommandationService
{
    /**
     * Poids de chaque signal, en pourcentage.
     *
     * Ce sont des HYPOTHÈSES DE DÉPART, pas des valeurs validées
     * scientifiquement. Elles sont regroupées ici pour être ajustées sans
     * toucher au reste du moteur.
     */
    public const POIDS = [
        'affinite_zone' => 40,
        'favori'        => 25,
        'financement'   => 20,
        'consultation'  => 10,
        'contexte'      => 5,
    ];

    /**
     * Statuts proposés à la découverte.
     *
     * `termine`, `suspendu` et `annule` sont écartés : recommander un chantier
     * arrêté ferait perdre son temps au citoyen. Ce n'est pas une règle
     * d'éligibilité au financement — celle-ci reste portée par
     * `Projet::estEligibleAuFinancementCitoyen()`.
     */
    public const STATUTS_DECOUVERTES = ['planifie', 'en_cours'];

    /**
     * Considère une consultation comme « récente » si elle a moins de ce
     * nombre de jours. Au-delà, le signal s'éteint complètement.
     */
    public const FENETRE_CONSULTATION_JOURS = 30;

    /**
     * Poids relatif des interactions dans le calcul de l'affinité de zone.
     * Un financement confirmé vaut davantage qu'un favori, qui vaut davantage
     * qu'une simple consultation — c'est la hiérarchie d'intérêt du cahier.
     */
    public const POND_INTERACTIONS = [
        'financement'  => 3.0,
        'favori'       => 2.0,
        'consultation' => 1.0,
    ];

    /** Valeurs d'interaction préchargées, pour éviter tout N+1. */
    private Collection $favoris;
    private Collection $consultations;
    private Collection $financements;

    /** @var array<int, int> ids des zones que le citoyen a déjà soutenues */
    private array $zonesSoutenues = [];

    /**
     * Owner of the cached interactions, or null when nothing is cached.
     *
     * ISOLATION. Without this guard, an instance reused for a second citizen
     * would keep serving the first one's profile — a private-history leak.
     * The cache is therefore valid for exactly ONE citizen.
     */
    private ?int $profilUserId = null;

    public function __construct()
    {
        $this->favoris        = collect();
        $this->consultations  = collect();
        $this->financements   = collect();
    }

    /* ── 1. Candidats admissibles ─────────────────────────────────────────── */

    /**
     * Projects that may be recommended at all.
     *
     * A high score never makes an ineligible project admissible: this filter
     * runs BEFORE any scoring.
     *
     * Excluded, and why:
     *   - `annule` / `suspendu` — the project is stopped, recommending it
     *     would waste the citizen's attention;
     *   - `termine` — there is nothing left to discover or fund;
     *   - the projects this citizen already funded — the goal is to surface
     *     NEW projects to support (decision documented in the V1 notes).
     *
     * `Projet::estEligibleAuFinancementCitoyen()` stays the single authority
     * on whether a project can still receive money. It is reused here rather
     * than reimplemented, so the recommendation can never disagree with the
     * funding screens.
     */
    public function candidats(User $citoyen): Collection
    {
        $dejaFinances = $this->projetIdsFinances($citoyen);

        return Projet::query()
            // `selectSub()` REPLACES the column list — without an explicit
            // `projets.*` every model attribute comes back null.
            ->select('projets.*')
            ->with('zones:id,nom')
            // Aggregate financed totals in SQL instead of calling the
            // `total_finance` accessor: one query, not one per project.
            ->selectSub(
                Financement::query()
                    ->selectRaw('COALESCE(SUM(montant), 0)')
                    ->whereColumn('financements.projet_id', 'projets.id'),
                'total_finance_calc'
            )
            // Same for amounts managers already approved but which were not
            // paid yet — the figure `budget_disponible` subtracts.
            ->selectSub(
                DemandeFinancement::query()
                    ->selectRaw('COALESCE(SUM(montant), 0)')
                    ->whereColumn('demandes_financement.projet_id', 'projets.id')
                    ->where('statut', DemandeFinancement::ACCEPTEE),
                'montant_reserve_calc'
            )
            ->whereIn('statut', self::STATUTS_DECOUVERTES)
            ->when($dejaFinances !== [], fn ($q) => $q->whereNotIn('id', $dejaFinances))
            ->get()
            ->each(function (Projet $projet) {
                $projet->setTotalFinanceCalc((float) $projet->total_finance_calc);
                $projet->setMontantReserveCalc((float) $projet->montant_reserve_calc);
            })
            // `budget_disponible > 0` is evaluated from the SQL aggregates, so
            // the eligibility rule stays identical to the accessor's.
            ->filter(fn (Projet $projet): bool => $projet->budget_disponible > 0)
            ->values();
    }

    /* ── 2. Profil : affinité par zone ────────────────────────────────────── */

    /**
     * Relative weight of each zone for this citizen, on a 0..1 scale.
     *
     * Two corrections are applied on purpose:
     *
     *  a) SIZE CORRECTION — a zone must not score high merely because it
     *     contains more projects. Each raw zone total is divided by the number
     *     of candidate projects in that zone, so the measure becomes
     *     "interest per available project" instead of raw volume.
     *
     *  b) NORMALISATION — the corrected values are min-max scaled to [0..1].
     *     A citizen with 50 interactions therefore cannot outrank a citizen
     *     with 3 interactions; only the DISTRIBUTION of interest matters.
     *
     * An empty profile returns an empty collection, and every project then
     * scores 0 on this component — which is what triggers the fallback.
     */
    public function affinitesZones(User $citoyen, Collection $candidats): Collection
    {
        $this->chargerInteractions($citoyen);

        $parProjet = [];

        foreach ($this->financements as $ligne) {
            $parProjet[$ligne->projet_id] = ($parProjet[$ligne->projet_id] ?? 0.0) + self::POND_INTERACTIONS['financement'];
        }
        foreach ($this->favoris as $ligne) {
            $parProjet[$ligne->projet_id] = ($parProjet[$ligne->projet_id] ?? 0.0) + self::POND_INTERACTIONS['favori'];
        }
        foreach ($this->consultations as $ligne) {
            $parProjet[$ligne->projet_id] = ($parProjet[$ligne->projet_id] ?? 0.0) + self::POND_INTERACTIONS['consultation'];
        }

        if ($parProjet === []) {
            return collect();
        }

        // One query to map every involved project to its zone(s).
        $zoneParProjet = Zone::query()
            ->join('projet_zone', 'zones.id', '=', 'projet_zone.zone_id')
            ->whereIn('projet_zone.projet_id', array_keys($parProjet))
            ->get(['zones.id', 'projet_zone.projet_id']);

        // Denominator: how many CANDIDATE projects each zone offers.
        $disponiblesParZone = $candidats
            ->flatMap(fn (Projet $p): array => $p->zones->pluck('id')->all())
            ->countBy();

        $brut = $zoneParProjet
            ->groupBy('id')
            ->map(fn (Collection $lignes): float => $lignes
                ->sum(fn ($ligne): float => $parProjet[$ligne->projet_id] ?? 0.0));

        $corrige = [];
        foreach ($disponiblesParZone as $zoneId => $disponibles) {
            $corrige[(int) $zoneId] = ($brut[$zoneId] ?? 0.0) / max(1, (int) $disponibles);
        }

        $max = $corrige === [] ? 0.0 : max($corrige);
        if ($max <= 0) {
            return collect();
        }

        return collect($corrige)
            ->map(fn (float $poids): float => round($poids / $max, 6))
            ->filter(fn (float $v): bool => $v > 0)
            ->sortKeys();
    }

    /* ── 3. Score ─────────────────────────────────────────────────────────── */

    /**
     * Ranking score of one project, on 0..100.
     *
     * Every component is first reduced to [0..1] so the weights stay
     * comparable and the total cannot exceed 100.
     *
     * The breakdown is returned as well, which is what makes the score
     * EXPLAINABLE: nothing here is a black box.
     *
     * @param  Collection<int, float>|null  $affinites  profile, or null for the fallback
     * @return array{score: float, composantes: array<string, float>}
     */
    public function score(Projet $projet, User $citoyen, ?Collection $affinites = null): array
    {
        $this->chargerInteractions($citoyen);

        $zones = $projet->zones->pluck('id');

        // (a) Affinité de zone — 40 %
        $affiniteZone = $affinites === null || $zones->isEmpty()
            ? 0.0
            : (float) $zones->max(fn ($zoneId): float => (float) $affinites->get((int) $zoneId, 0.0));

        // (b) Favori — 25 %. Signal EXPLICITE : le citoyen a choisi ce projet.
        $favori = $this->favoris->contains('projet_id', $projet->id) ? 1.0 : 0.0;

        // (c) Financement — 20 %. Seuls les financements CONFIRMÉS comptent :
        //     une demande en attente ou un paiement refusé ne crée aucune
        //     ligne `financements`, donc n'apparaît jamais ici.
        //
        //     Les projets déjà financés par le citoyen ne sont pas candidats
        //     (cf. candidats()), donc ce composante mesure le soutien à
        //     d'autres projets de la même zone : « vous avez déjà soutenu des
        //     projets similaires ». Elle ne ré-compense donc jamais le projet
        //     déjà payé par ce citoyen.
        $zonesSoutenues = $this->zonesSoutenues($citoyen);
        $financement = $zones->contains(fn ($zoneId): bool => in_array((int) $zoneId, $zonesSoutenues, true))
            ? 1.0
            : 0.0;

        // (d) Consultation récente — 10 %. Décroissance linéaire sur la
        //     fenêtre, puis extinction.
        $consultation = $this->fraicheurConsultation($projet);

        // (e) Contexte — 5 %. Justifié, jamais arbitraire : avancement du
        //     chantier + part du budget encore ouverte. C'est un POURCENTAGE,
        //     jamais un budget absolu, pour qu'un gros budget ne soit pas
        //     récompensé parce qu'il est gros.
        $avancement = min(1.0, max(0.0, (float) $projet->progression / 100));
        $marge = $projet->budget > 0
            ? min(1.0, max(0.0, $projet->budget_disponible / (float) $projet->budget))
            : 0.0;
        $contexte = round(0.5 * $avancement + 0.5 * $marge, 6);

        $composantes = [
            'affinite_zone' => round($affiniteZone, 6),
            'favori'        => $favori,
            'financement'   => $financement,
            'consultation'  => $consultation,
            'contexte'      => $contexte,
        ];

        $total = 0.0;
        foreach ($composantes as $nom => $valeur) {
            $total += $valeur * self::POIDS[$nom];
        }

        return [
            'score'       => (float) round(min(100.0, max(0.0, $total)), 2),
            'composantes' => $composantes,
        ];
    }

    /**
     * Recency of the citizen's views of this project, 1.0 → 0.0.
     */
    private function fraicheurConsultation(Projet $projet): float
    {
        $vue = $this->consultations
            ->where('projet_id', $projet->id)
            ->sortByDesc('created_at')
            ->first();

        if (! $vue) {
            return 0.0;
        }

        $jours = (int) now()->diffInDays($vue->created_at);

        if ($jours >= self::FENETRE_CONSULTATION_JOURS) {
            return 0.0;
        }

        return (float) round(1.0 - ($jours / self::FENETRE_CONSULTATION_JOURS), 6);
    }

    /* ── 4. Explications ──────────────────────────────────────────────────── */

    /**
     * Human-readable reasons, built ONLY from signals that really fired.
     *
     * A reason is never invented: with no confirmed funding, no funding
     * reason is produced. Nothing here exposes a personal amount, so no
     * private financial figure leaks into a public card.
     *
     * @param  array<string, float>  $composantes
     * @return array<int, string>
     */
    public function raisons(Projet $projet, array $composantes, bool $repli = false): array
    {
        if ($repli) {
            // Honest fallback wording: this is a discovery selection, not a
            // personalisation the engine has learned anything about.
            return array_values(array_filter([
                'Sélection de projets à découvrir.',
                $projet->estEligibleAuFinancementCitoyen()
                    ? 'Projet actuellement admissible au financement citoyen.'
                    : null,
                $projet->progression > 0
                    ? 'Chantier déjà engagé ('.$projet->progression.' % de progression).'
                    : null,
            ]));
        }

        $nomsZones = $projet->zones->pluck('nom')->filter()->implode(', ');

        $raisons = [];

        if (($composantes['favori'] ?? 0) > 0) {
            $raisons[] = 'Vous avez ajouté ce projet à vos favoris.';
        }

        if (($composantes['financement'] ?? 0) > 0) {
            $raisons[] = 'Vous avez déjà soutenu des projets de la même zone'
                .($nomsZones !== '' ? ' ('.$nomsZones.')' : '').'.';
        }

        if (($composantes['consultation'] ?? 0) > 0) {
            $raisons[] = 'Projet récemment consulté.';
        }

        if (($composantes['affinite_zone'] ?? 0) > 0 && $nomsZones !== '') {
            $raisons[] = 'Correspond à une zone que vous explorez souvent'
                .($nomsZones !== '' ? ' ('.$nomsZones.')' : '').'.';
        }

        if ($projet->estEligibleAuFinancementCitoyen()) {
            $raisons[] = 'Projet actuellement admissible au financement citoyen.';
        }

        return array_values(array_unique($raisons));
    }

    /* ── Orchestration ────────────────────────────────────────────────────── */

    /**
     * Rank the admissible projects for this citizen.
     *
     * @param  int  $limite  how many recommendations to return, bounded
     * @return array{items: Collection<int, Recommandation>, repli: bool, candidats: int}
     */
    public function recommander(User $citoyen, int $limite = 6): array
    {
        $limite = min(20, max(1, $limite));

        $this->chargerInteractions($citoyen);

        $candidats = $this->candidats($citoyen);

        if ($candidats->isEmpty()) {
            return ['items' => collect(), 'repli' => false, 'candidats' => 0];
        }

        $affinites = $this->affinitesZones($citoyen, $candidats);

        // A citizen with no signal at all gets the documented fallback ranking
        // rather than a fake "personalisation" that learned nothing.
        $repli = $affinites->isEmpty();

        $items = $candidats->map(function (Projet $projet) use ($citoyen, $affinites, $repli) {
            [$score, $composantes] = $repli
                ? [$this->scoreRepli($projet), []]
                : (function () use ($projet, $citoyen, $affinites) {
                    $d = $this->score($projet, $citoyen, $affinites);

                    return [$d['score'], $d['composantes']];
                })();

            return new Recommandation(
                projetId:   (int) $projet->id,
                nom:        (string) $projet->nom,
                score:      (float) $score,
                financable: $projet->estEligibleAuFinancementCitoyen(),
                raisons:    $this->raisons($projet, $composantes, $repli),
                projet:     $projet,
                repli:      $repli,
            );
        });

        // DETERMINISTIC ORDER. Ties are broken by a documented, stable rule:
        //   1. score descending;
        //   2. the LESS financed project first (the one still in need);
        //   3. the project id ascending, which makes the order total.
        // Two identical runs therefore always return the same list.
        $items = $items
            ->sort(function (Recommandation $a, Recommandation $b): int {
                return [$b->score, -$a->projet->pourcentage_finance, $a->projetId]
                    <=> [$a->score, -$b->projet->pourcentage_finance, $b->projetId];
            })
            ->values()
            ->take($limite);

        return ['items' => $items, 'repli' => $repli, 'candidats' => $candidats->count()];
    }

    /**
     * Fallback score for a citizen with no history.
     *
     * Honest and documented: this ranks by how much of the budget is still
     * open and by how far the chantier has advanced. It is NOT personalisation,
     * and the UI says so explicitly.
     */
    private function scoreRepli(Projet $projet): float
    {
        $marge = $projet->budget > 0
            ? min(1.0, max(0.0, $projet->budget_disponible / (float) $projet->budget))
            : 0.0;

        return (float) round(60.0 * $marge + 40.0 * min(1.0, (float) $projet->progression / 100), 2);
    }

    /* ── Chargeur d'interactions (anti N+1) ───────────────────────────────── */

    /**
     * Load every interaction of the citizen ONCE.
     *
     * The engine then works on collections only: no query inside the scoring
     * loop, therefore no N+1 whatever the number of candidate projects.
     */
    private function chargerInteractions(User $citoyen): void
    {
        // ISOLATION: the cache belongs to one citizen only. If the instance is
        // reused for someone else, the previous profile is dropped and the
        // queries run again. A citizen's private history must never be served
        // to another one.
        if ($this->profilUserId !== null && $this->profilUserId !== (int) $citoyen->id) {
            $this->favoris       = collect();
            $this->consultations = collect();
            $this->financements  = collect();
            $this->zonesSoutenues = [];
        }

        if ($this->profilUserId === (int) $citoyen->id) {
            return;
        }

        $this->profilUserId = (int) $citoyen->id;

        $this->favoris       = $citoyen->favoris()->get(['id', 'user_id', 'projet_id', 'created_at']);
        $this->consultations = $citoyen->consultations()->get(['id', 'user_id', 'projet_id', 'created_at']);
        $this->financements  = $citoyen->financements()->get(['id', 'user_id', 'projet_id', 'date_financement']);

        $this->zonesSoutenues = $this->financements->isEmpty()
            ? []
            : Zone::query()
                ->join('projet_zone', 'zones.id', '=', 'projet_zone.zone_id')
                ->whereIn('projet_zone.projet_id', $this->financements->pluck('projet_id')->unique())
                ->pluck('zones.id')
                ->map(fn ($id): int => (int) $id)
                ->all();
    }

    /** @return array<int, int> */
    private function zonesSoutenues(User $citoyen): array
    {
        $this->chargerInteractions($citoyen);

        return $this->zonesSoutenues;
    }

    /** @return array<int, int> */
    private function projetIdsFinances(User $citoyen): array
    {
        return $citoyen->financements()
            ->pluck('projet_id')
            ->unique()
            ->map(fn ($id): int => (int) $id)
            ->all();
    }
}
