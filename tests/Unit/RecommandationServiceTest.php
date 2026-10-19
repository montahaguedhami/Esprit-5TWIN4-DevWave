<?php

namespace Tests\Unit;

use App\Models\ConsultationProjet;
use App\Models\Favori;
use App\Models\Financement;
use App\Models\Projet;
use App\Models\Role;
use App\Models\User;
use App\Models\Zone;
use App\Services\Recommandation\RecommandationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

/**
 * Moteur de recommandation V1 — déterministe, basé sur des règles.
 *
 * Ce n'est PAS un modèle de Machine Learning : aucune dépendance ML, aucun
 * entraînement, aucun appel réseau. Ces tests vérifient le classement, son
 * explicabilité et sa reproductibilité.
 */
class RecommandationServiceTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    private RecommandationService $moteur;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
        $this->moteur = new RecommandationService();
    }

    /* ── Helpers ─────────────────────────────────────────────────────────── */

    private function citoyen(): User
    {
        return $this->createUserWithRole(Role::CITOYEN);
    }

    /**
     * A project with an explicit zone and budget, so its eligibility and its
     * score components are predictable.
     */
    private function projet(array $attrs = [], ?Zone $zone = null): Projet
    {
        $projet = Projet::factory()->create(array_merge([
            'statut'  => 'en_cours',
            'budget'  => 1000000,
        ], $attrs));

        if ($zone instanceof Zone) {
            $projet->zones()->attach($zone->id);
        }

        return $projet->fresh('zones');
    }

    private function zone(string $nom): Zone
    {
        return Zone::factory()->create(['nom' => $nom]);
    }

    /* ── 1. Bornes et déterminisme ───────────────────────────────────────── */

    public function test_le_score_est_toujours_compris_entre_0_et_100(): void
    {
        $citoyen = $this->citoyen();
        $zone    = $this->zone('Sahel');

        // A citizen with every signal at maximum on the same project.
        $projet = $this->projet(['statut' => 'en_cours'], $zone);
        Favori::create(['user_id' => $citoyen->id, 'projet_id' => $projet->id]);
        ConsultationProjet::create(['user_id' => $citoyen->id, 'projet_id' => $projet->id, 'bucket' => ConsultationProjet::bucketCourant()]);

        $autre = $this->projet(['statut' => 'en_cours', 'budget' => 2000000], $zone);
        $this->financer($autre, $citoyen, 500000);

        foreach ($this->moteur->candidats($citoyen) as $candidat) {
            $detail = $this->moteur->score($candidat, $citoyen, $this->moteur->affinitesZones($citoyen, $this->moteur->candidats($citoyen)));

            $this->assertGreaterThanOrEqual(0.0, $detail['score']);
            $this->assertLessThanOrEqual(100.0, $detail['score']);
        }
    }

    public function test_les_poids_sont_bien_une_somme_de_100(): void
    {
        // The weights are documented hypotheses, but they must still add up —
        // otherwise the 0..100 scale would be a lie.
        $this->assertSame(100, array_sum(RecommandationService::POIDS));
    }

    public function test_les_composantes_reduites_au_plus_1_ne_depasse_jamais_le_total(): void
    {
        $citoyen = $this->citoyen();
        $zone    = $this->zone('Sahel');
        $projet  = $this->projet([], $zone);

        Favori::create(['user_id' => $citoyen->id, 'projet_id' => $projet->id]);
        ConsultationProjet::create(['user_id' => $citoyen->id, 'projet_id' => $projet->id, 'bucket' => ConsultationProjet::bucketCourant()]);

        $autre = $this->projet([], $zone);
        $this->financer($autre, $citoyen, 100000);

        $candidats = $this->moteur->candidats($citoyen);
        $detail    = $this->moteur->score($candidats->first(), $citoyen, $this->moteur->affinitesZones($citoyen, $candidats));

        foreach ($detail['composantes'] as $nom => $valeur) {
            $this->assertGreaterThanOrEqual(0.0, $valeur, "Composante $nom négative.");
            $this->assertLessThanOrEqual(1.0, $valeur, "Composante $nom supérieure à 1.");
        }
    }

    /* ── 2. Signal favori ────────────────────────────────────────────────── */

    public function test_un_projet_favori_recoit_le_bonus_correspondant(): void
    {
        $citoyen = $this->citoyen();
        $zone    = $this->zone('Sahel');

        $favori  = $this->projet(['nom' => 'Projet favori'], $zone);
        $neutre  = $this->projet(['nom' => 'Projet neutre'], $zone);

        Favori::create(['user_id' => $citoyen->id, 'projet_id' => $favori->id]);

        $candidats = $this->moteur->candidats($citoyen);
        $affinites = $this->moteur->affinitesZones($citoyen, $candidats);

        $scoreFavori = $this->moteur->score($favori->fresh('zones'), $citoyen, $affinites);
        $scoreNeutre = $this->moteur->score($neutre->fresh('zones'), $citoyen, $affinites);

        $this->assertSame(1.0, $scoreFavori['composantes']['favori']);
        $this->assertSame(0.0, $scoreNeutre['composantes']['favori']);
        $this->assertGreaterThan($scoreNeutre['score'], $scoreFavori['score']);
    }

    /* ── 3. Seuls les financements confirmés comptent ────────────────────── */

    public function test_les_financements_non_confirmes_ne_sont_pas_consideres(): void
    {
        // A pending funding request or a refused payment creates NO row in
        // `financements`. The engine reads that table only, so a mere request
        // must leave the funding component at zero.
        $citoyen = $this->citoyen();
        $zone    = $this->zone('Sahel');
        $projet  = $this->projet([], $zone);

        // A request that a manager never approved.
        \App\Models\DemandeFinancement::create([
            'projet_id' => $projet->id,
            'user_id'   => $citoyen->id,
            'montant'   => 50000,
            'devise'    => 'TND',
            'statut'    => \App\Models\DemandeFinancement::EN_ATTENTE,
        ]);

        $candidats = $this->moteur->candidats($citoyen);
        $detail    = $this->moteur->score($projet->fresh('zones'), $citoyen, $this->moteur->affinitesZones($citoyen, $candidats));

        $this->assertSame(0.0, $detail['composantes']['financement']);
        $this->assertSame(0.0, $detail['composantes']['affinite_zone']);
    }

    public function test_un_financement_confirme_est_pris_en_compte(): void
    {
        $citoyen = $this->citoyen();
        $zone    = $this->zone('Sahel');

        $soutenu = $this->projet([], $zone);
        $cible   = $this->projet(['nom' => 'Projet cible de la zone'], $zone);

        $this->financer($soutenu, $citoyen, 100000);

        $candidats = $this->moteur->candidats($citoyen);
        $detail    = $this->moteur->score($cible->fresh('zones'), $citoyen, $this->moteur->affinitesZones($citoyen, $candidats));

        // The citizen already backed another project of the same zone.
        $this->assertSame(1.0, $detail['composantes']['financement']);
    }

    /* ── 4. Préférences par zone ─────────────────────────────────────────── */

    public function test_les_preferences_de_zone_influencent_le_classement(): void
    {
        $citoyen = $this->citoyen();
        $sahel   = $this->zone('Sahel');
        $tunis   = $this->zone('Grand Tunis');

        // Every interaction lands in the Sahel zone.
        $amis = $this->projet([], $sahel);
        $this->financer($amis, $citoyen, 100000);
        Favori::create(['user_id' => $citoyen->id, 'projet_id' => $amis->id]);

        // Two comparable candidates, one per zone.
        $candSahel = $this->projet(['nom' => 'Candidat Sahel'], $sahel);
        $candTunis = $this->projet(['nom' => 'Candidat Tunis'], $tunis);

        $candidats = $this->moteur->candidats($citoyen);
        $affinites = $this->moteur->affinitesZones($citoyen, $candidats);

        $this->assertTrue($affinites->has($sahel->id));
        $this->assertFalse($affinites->has($tunis->id), 'Une zone sans interaction ne doit pas avoir d\'affinité.');

        $scoreSahel = $this->moteur->score($candSahel->fresh('zones'), $citoyen, $affinites);
        $scoreTunis = $this->moteur->score($candTunis->fresh('zones'), $citoyen, $affinites);

        $this->assertGreaterThan($scoreTunis['score'], $scoreSahel['score']);
    }

    public function test_une_zone_avec_plus_de_projets_ne_domine_pas_pour_autant(): void
    {
        // SIZE CORRECTION. The Sahel zone holds many more projects; without the
        // correction it would win on volume alone, which is not interest.
        $citoyen = $this->citoyen();
        $petite  = $this->zone('Petite zone');
        $grande  = $this->zone('Grande zone');

        // Both zones receive the SAME interaction volume.
        $this->projet([], $petite);
        $this->projet([], $grande);
        Favori::create(['user_id' => $citoyen->id, 'projet_id' => $this->projet([], $petite)->id]);
        Favori::create(['user_id' => $citoyen->id, 'projet_id' => $this->projet([], $grande)->id]);

        // The large zone gets extra uninteresting projects.
        foreach (range(1, 6) as $i) {
            $this->projet(['nom' => 'Remplissage grande zone '.$i], $grande);
        }

        $candidats = $this->moteur->candidats($citoyen);
        $affinites = $this->moteur->affinitesZones($citoyen, $candidats);

        // With 1 favourite over 2 candidates the small zone scores 1.0; with
        // 1 favourite over 8 candidates the large zone scores far lower.
        $this->assertSame(1.0, $affinites->get($petite->id));
        $this->assertLessThan(0.5, $affinites->get($grande->id));
    }

    /* ── 5. Citoyen sans historique ──────────────────────────────────────── */

    public function test_un_citoyen_sans_historique_recoit_un_classement_de_repli(): void
    {
        $citoyen = $this->citoyen();

        $this->projet(['nom' => 'Premier a decouvrir'], ['statut' => 'en_cours'][0] ?? null);
        $this->projet(['nom' => 'Second a decouvrir']);

        $resultat = $this->moteur->recommander($citoyen, 5);

        $this->assertTrue($resultat['repli'], 'Un citoyen sans signal doit basculer en repli.');
        $this->assertNotEmpty($resultat['items']);

        foreach ($resultat['items'] as $item) {
            $this->assertTrue($item->repli);
            // Honest wording: never claims the selection was learned.
            $this->assertStringContainsString('Sélection de projets à découvrir', implode(' ', $item->raisons));
        }
    }

    public function test_le_repli_ne_jamais_presente_le_classement_comme_une_personnalisation(): void
    {
        $citoyen = $this->citoyen();
        $this->projet();

        $resultat = $this->moteur->recommander($citoyen, 5);

        $this->assertTrue($resultat['repli']);

        foreach ($resultat['items'] as $item) {
            $explications = implode(' ', $item->raisons);
            $this->assertStringNotContainsString('favoris', $explications);
            $this->assertStringNotContainsString('soutenu', $explications);
            $this->assertStringNotContainsString('consulté', $explications);
        }
    }

    /* ── 6. Admissibilité ────────────────────────────────────────────────── */

    public function test_les_projets_non_admissibles_ne_sont_pas_proposes(): void
    {
        $citoyen = $this->citoyen();

        $annule     = $this->projet(['statut' => 'annule']);
        $termine    = $this->projet(['statut' => 'termine']);
        $suspendu   = $this->projet(['statut' => 'suspendu']);
        $valide     = $this->projet(['statut' => 'en_cours']);

        $candidats = $this->moteur->candidats($citoyen)->pluck('id');

        $this->assertContains($valide->id, $candidats);
        $this->assertNotContains($annule->id, $candidats);
        $this->assertNotContains($termine->id, $candidats);
        $this->assertNotContains($suspendu->id, $candidats);
    }

    public function test_un_projet_entierement_finance_n_est_plus_candidat(): void
    {
        $citoyen = $this->citoyen();
        $projet  = $this->projet(['budget' => 100000]);

        $this->financer($projet, $citoyen, 100000);

        $this->assertNotContains($projet->id, $this->moteur->candidats($citoyen)->pluck('id'));
    }

    public function test_un_projet_deja_financie_par_le_citoyen_n_est_plus_recommande(): void
    {
        // Documented decision: the goal is to surface NEW projects to support.
        $citoyen = $this->citoyen();
        $zone    = $this->zone('Sahel');
        $projet  = $this->projet([], $zone);

        $this->financer($projet, $citoyen, 50000);

        $this->assertNotContains($projet->id, $this->moteur->candidats($citoyen)->pluck('id'));
    }

    public function test_un_score_eleve_ne_peut_pas_rendre_admissible_un_projet_ineligible(): void
    {
        // The filter runs BEFORE scoring: a cancelled project could otherwise
        // accumulate signals and resurface.
        $citoyen = $this->citoyen();
        $zone    = $this->zone('Sahel');
        $projet  = $this->projet(['statut' => 'annule'], $zone);

        Favori::create(['user_id' => $citoyen->id, 'projet_id' => $projet->id]);

        $resultat = $this->moteur->recommander($citoyen, 20);

        $this->assertNotContains($projet->id, $resultat['items']->pluck('projetId'));
    }

    public function test_les_projets_non_financables_sont_marques_comme_tels(): void
    {
        // Découverte vs éligibilité : un projet peut rester intéressant tout
        // en ne pouvant plus recevoir d'argent. Il ne doit jamais être présenté
        // comme finançable.
        $citoyen = $this->citoyen();
        $projet  = $this->projet(['budget' => 100000, 'statut' => 'en_cours']);

        // Filled by a manager-entered funding, which has no citizen author.
        $this->financer($projet, $citoyen, 90000, true);

        $candidats = $this->moteur->candidats($citoyen)->keyBy('id');
        $this->assertTrue($candidats->has($projet->id), 'Encore de la place : le projet reste candidat.');

        $resultat = $this->moteur->recommander($citoyen, 20);
        $item     = $resultat['items']->firstWhere('projetId', $projet->id);

        $this->assertTrue($item->financable, 'Il reste 10 000 DT : le projet est finançable.');
    }

    /* ── 7. Doublons, égalités, déterminisme ─────────────────────────────── */

    public function test_aucun_doublon_n_est_retourne(): void
    {
        $citoyen = $this->citoyen();
        $zone    = $this->zone('Sahel');

        foreach (range(1, 8) as $i) {
            $this->projet(['nom' => 'Projet '.$i], $zone);
        }

        $resultat = $this->moteur->recommander($citoyen, 20);

        $this->assertSame($resultat['items']->count(), $resultat['items']->pluck('projetId')->unique()->count());
    }

    public function test_les_egalites_de_score_sont_departagees_de_facon_deterministe(): void
    {
        $citoyen = $this->citoyen();
        $zone    = $this->zone('Sahel');

        // Identical characteristics → identical score → the tie-break rule
        // (less financed first, then id) decides, and it is stable.
        foreach (range(1, 5) as $i) {
            $this->projet(['nom' => 'Jumeau '.$i, 'budget' => 500000], $zone);
        }

        $premier  = $this->moteur->recommander($citoyen, 20);
        $second   = $this->moteur->recommander($citoyen, 20);

        $this->assertSame(
            $premier['items']->pluck('projetId')->all(),
            $second['items']->pluck('projetId')->all(),
            'Deux exécutions identiques doivent produire le même ordre.'
        );
    }

    public function test_un_profil_ne_fuit_jamais_vers_un_autre_citoyen(): void
    {
        // ISOLATION. The service caches interactions to avoid N+1. If that
        // cache were not scoped to its owner, reusing the same instance for a
        // second citizen would silently serve the first one's private history.
        $alice  = $this->citoyen();
        $bob    = $this->citoyen();
        $zone   = $this->zone('Sahel');
        $cible  = $this->projet(['nom' => 'Projet isole'], $zone);

        Favori::create(['user_id' => $alice->id, 'projet_id' => $cible->id]);

        // The SAME instance handles both citizens, back to back.
        $affinitesAlice = $this->moteur->affinitesZones($alice, $this->moteur->candidats($alice));
        $affinitesBob   = $this->moteur->affinitesZones($bob, $this->moteur->candidats($bob));

        $this->assertTrue($affinitesAlice->has($zone->id), 'Alice a un favori dans cette zone.');
        $this->assertFalse(
            $affinitesBob->has($zone->id),
            'Bob n\'a aucune interaction : le profil d\'Alice ne doit jamais lui être servi.'
        );

        $scoreAlice = $this->moteur->score($cible->fresh('zones'), $alice, $affinitesAlice);
        $scoreBob   = $this->moteur->score($cible->fresh('zones'), $bob, $affinitesBob);

        $this->assertSame(1.0, $scoreAlice['composantes']['favori']);
        $this->assertSame(0.0, $scoreBob['composantes']['favori']);
    }

    public function test_les_meme_donnees_produisent_toujours_le_meme_resultat(): void    {
        $citoyen = $this->citoyen();
        $zone    = $this->zone('Sahel');

        $cible = $this->projet(['nom' => 'Cible'], $zone);
        Favori::create(['user_id' => $citoyen->id, 'projet_id' => $cible->id]);

        foreach (range(1, 4) as $i) {
            $this->projet(['nom' => 'Autre '.$i], $zone);
        }

        $a = $this->moteur->recommander($citoyen, 6);
        $b = $this->moteur->recommander($citoyen, 6);

        $this->assertEqualsCanonicalizing(
            $a['items']->pluck('score')->all(),
            $b['items']->pluck('score')->all()
        );
        $this->assertSame($a['items']->pluck('projetId')->all(), $b['items']->pluck('projetId')->all());
    }

    public function test_deux_citoyens_aux_interactions_differentes_peuvent_obtenir_des_classements_different(): void
    {
        $alice  = $this->citoyen();
        $bob    = $this->citoyen();
        $sahel  = $this->zone('Sahel');
        $tunis  = $this->zone('Grand Tunis');

        $pSahel = $this->projet(['nom' => 'Projet du Sahel'], $sahel);
        $pTunis = $this->projet(['nom' => 'Projet de Tunis'], $tunis);

        // Alice loves the Sahel, Bob loves Tunis.
        Favori::create(['user_id' => $alice->id, 'projet_id' => $pSahel->id]);
        Favori::create(['user_id' => $bob->id, 'projet_id' => $pTunis->id]);

        $ordreAlice = $this->moteur->recommander($alice, 6)['items']->pluck('projetId')->all();
        $ordreBob   = $this->moteur->recommander($bob, 6)['items']->pluck('projetId')->all();

        $this->assertNotSame($ordreAlice, $ordreBob);
    }

    /* ── 8. Limite ───────────────────────────────────────────────────────── */

    public function test_la_limite_de_resultats_est_bornee(): void
    {
        $citoyen = $this->citoyen();

        // More candidates than the hard ceiling, so the bound is observable.
        foreach (range(1, 25) as $i) {
            $this->projet(['nom' => 'Propose '.$i]);
        }

        // A huge limit must not turn the endpoint into a catalogue dump.
        $this->assertSame(20, $this->moteur->recommander($citoyen, 9999)['items']->count());
        $this->assertSame(1, $this->moteur->recommander($citoyen, -5)['items']->count());
        $this->assertSame(3, $this->moteur->recommander($citoyen, 3)['items']->count());
        $this->assertSame(25, $this->moteur->candidats($citoyen)->count());
    }

    public function test_aucun_candidat_renvoie_une_reponse_vide_sans_erreur(): void
    {
        $citoyen = $this->citoyen();
        $this->projet(['statut' => 'annule']);

        $resultat = $this->moteur->recommander($citoyen, 6);

        $this->assertSame(0, $resultat['candidats']);
        $this->assertCount(0, $resultat['items']);
    }

    /* ── Helpers ─────────────────────────────────────────────────────────── */

    /**
     * Record a CONFIRMED funding, the only kind the engine reads.
     *
     * @param  bool  $managerOnly  emulate a manager-entered funding, which has
     *                             no citizen author
     */
    private function financer(Projet $projet, User $citoyen, float $montant, bool $managerOnly = false): Financement
    {
        $financement = new Financement([
            'projet_id'        => $projet->id,
            'source'           => 'Financement citoyen',
            'montant'          => $montant,
            'date_financement' => today()->toDateString(),
        ]);

        if (! $managerOnly) {
            $financement->user_id = $citoyen->id;
        }

        $financement->save();

        return $financement;
    }
}
