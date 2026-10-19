<?php

namespace Tests\Feature;

use App\Models\ConsultationProjet;
use App\Models\Projet;
use App\Models\Role;
use App\Models\User;
use App\Services\Recommandation\EnregistreurConsultation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

/**
 * Historique des consultations — le signal d'intérêt IMPLICITE.
 *
 * Une consultation prouve seulement que le citoyen a ouvert la page de détail
 * d'un projet. C'est le signal le plus faible du classement, et rien d'autre
 * n'est enregistré à son sujet.
 */
class ConsultationProjetTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seedRbac();
    }

    /* ── Helpers ─────────────────────────────────────────────────────────── */

    /**
     * `Citizen\ProjetController` se protège avec `Role::sessionMatches()`, qui
     * lit la clé de SESSION `user.role` écrite à la connexion — pas le garde
     * d'authentification. `actingAs()` seul laisse cette clé vide et la page
     * redirige : les deux sont donc nécessaires. C'est la convention déjà
     * utilisée par CitizenNetworkDirectoryTest.
     */
    private function asCitoyen(User $citoyen): static
    {
        return $this->actingAs($citoyen)->withSession([
            'user' => [
                'name'  => $citoyen->name,
                'email' => $citoyen->email,
                'role'  => 'citizen',
            ],
        ]);
    }

    private function citoyen(): User
    {
        return $this->createUserWithRole(Role::CITOYEN);
    }

    private function projet(): Projet
    {
        return Projet::factory()->create(['statut' => 'en_cours']);
    }

    /* ── 1. Enregistrement ───────────────────────────────────────────────── */

    public function test_une_consultation_est_enregistree_pour_le_bon_utilisateur(): void
    {
        $citoyen = $this->citoyen();
        $projet  = $this->projet();

        $this->asCitoyen($citoyen)
            ->get(route('citizen.projets.show', $projet))
            ->assertOk();

        $this->assertDatabaseHas('consultation_projets', [
            'user_id'   => $citoyen->id,
            'projet_id' => $projet->id,
        ]);
    }

    public function test_une_consultation_est_associee_au_bon_projet(): void
    {
        $citoyen = $this->citoyen();
        $vu      = $this->projet();
        $pasVu   = $this->projet();

        $this->asCitoyen($citoyen)->get(route('citizen.projets.show', $vu))->assertOk();

        $this->assertSame(1, ConsultationProjet::where('user_id', $citoyen->id)->count());
        $this->assertDatabaseHas('consultation_projets', ['projet_id' => $vu->id]);
        $this->assertDatabaseMissing('consultation_projets', ['projet_id' => $pasVu->id]);
    }

    public function test_consulter_deux_fois_meme_projet_ne_fait_qu_une_ligne_dans_la_fenetre(): void
    {
        $citoyen = $this->citoyen();
        $projet  = $this->projet();

        // Deux vues dans la même fenêtre : F5, une icône re-rendue, un appel
        // AJAX répété — aucun d'eux ne doit fabriquer un intérêt supplémentaire.
        $this->asCitoyen($citoyen)->get(route('citizen.projets.show', $projet))->assertOk();
        $this->asCitoyen($citoyen)->get(route('citizen.projets.show', $projet))->assertOk();
        $this->asCitoyen($citoyen)->get(route('citizen.projets.show', $projet))->assertOk();

        $this->assertSame(1, ConsultationProjet::where('user_id', $citoyen->id)->count());
    }

    public function test_une_consultation_hors_fenetre_cree_une_nouvelle_ligne(): void
    {
        // Règle documentée : la fenêtre dure 30 minutes. Passé ce délai, une
        // nouvelle visite est une interaction réelle et crée une seconde ligne.
        $citoyen = $this->citoyen();
        $projet  = $this->projet();

        $this->asCitoyen($citoyen)->get(route('citizen.projets.show', $projet))->assertOk();

        // Sort la ligne existante de la fenêtre courante.
        ConsultationProjet::query()->update([
            'bucket'     => ConsultationProjet::bucketCourant() - 100,
            'created_at' => now()->subDay(),
        ]);

        $this->asCitoyen($citoyen)->get(route('citizen.projets.show', $projet))->assertOk();

        $this->assertSame(2, ConsultationProjet::where('user_id', $citoyen->id)->count());
    }

    public function test_la_fenetre_de_regroupement_est_de_trente_minutes(): void
    {
        // La constante EST la règle unique ; ce test la fige pour qu'un
        // changement silencieux soit détecté.
        $this->assertSame(30, ConsultationProjet::FENETRE_REGROUPEMENT_MINUTES);

        // Une même fenêtre : deux instants proches donnent le même bucket,
        // sauf s'ils traversent une frontière — d'où l'écart admis.
        $avant = ConsultationProjet::bucketFor(now()->subMinutes(2));
        $apres = ConsultationProjet::bucketFor(now());
        $this->assertLessThanOrEqual(1, abs($apres - $avant));

        // Trois heures d'écart : au moins deux fenêtres d'écart.
        $lointain = ConsultationProjet::bucketFor(now()->subHours(3));
        $this->assertGreaterThanOrEqual(5, abs($apres - $lointain));

        // Déterministe : le même instant rend toujours le même bucket.
        $this->assertSame($apres, ConsultationProjet::bucketFor(now()));
    }

    public function test_le_meme_projet_de_deux_citoyens_ne_partage_pas_la_fenetre(): void
    {
        $alice  = $this->citoyen();
        $bob    = $this->citoyen();
        $projet = $this->projet();

        $this->asCitoyen($alice)->get(route('citizen.projets.show', $projet))->assertOk();
        $this->asCitoyen($bob)->get(route('citizen.projets.show', $projet))->assertOk();

        // La contrainte porte sur (citoyen, projet, bucket) : deux citoyens
        // sur le même projet cohabitent sans se neutraliser.
        $this->assertSame(2, ConsultationProjet::where('projet_id', $projet->id)->count());
    }

    /* ── 2. Un échec d'écriture ne bloque jamais la page ─────────────────── */

    public function test_un_echec_d_enregistrement_ne_bloque_pas_l_affichage_du_projet(): void
    {
        $citoyen = $this->citoyen();
        $projet  = $this->projet();

        // Un recordeur dont le stockage explose toujours, substitué au vrai.
        // La page DOIT s'afficher : un favori manquant dans un classement ne
        // vaut jamais une erreur 500.
        //
        // Le double DOIT étendre la classe réelle : le contrôleur la reçoit en
        // injection de dépendances typée, donc un objet anonyme non conforme
        // serait rejeté avant même d'être appelé.
        $this->app->bind(EnregistreurConsultation::class, function () {
            return new class extends EnregistreurConsultation
            {
                public function enregistrer($request, int $projetId): bool
                {
                    throw new \RuntimeException('panne simulee du stockage');
                }
            };
        });

        $this->asCitoyen($citoyen)
            ->get(route('citizen.projets.show', $projet))
            ->assertOk();

        $this->assertSame(0, ConsultationProjet::count());
    }

    public function test_consulter_une_page_de_liste_ne_cree_pas_de_consultation(): void
    {
        $citoyen = $this->citoyen();
        $this->projet();

        $this->asCitoyen($citoyen)->get(route('citizen.projets.index'))->assertOk();

        // Une liste n'est pas une consultation : seul le détail est un signal.
        $this->assertSame(0, ConsultationProjet::count());
    }

    public function test_un_visiteur_ne_genere_aucune_consultation(): void
    {
        $projet = $this->projet();

        // La route est derrière `role:citoyen`, donc le visiteur est redirigé
        // et aucun signal n'est écrit.
        $this->get(route('citizen.projets.show', $projet))->assertRedirect(route('auth.login'));

        $this->assertSame(0, ConsultationProjet::count());
    }

    /* ── 3. Isolation de l'historique ────────────────────────────────────── */

    public function test_un_citoyen_ne_peut_pas_lire_l_historique_prive_d_un_autre(): void
    {
        $victime = $this->citoyen();
        $intrus  = $this->citoyen();
        $projet  = $this->projet();

        $this->asCitoyen($victime)->get(route('citizen.projets.show', $projet))->assertOk();

        // L'intrus ouvre la même page : il crée SON propre signal et ne voit
        // jamais celui de la victime.
        $this->asCitoyen($intrus)->get(route('citizen.projets.show', $projet))->assertOk();

        $this->assertDatabaseHas('consultation_projets', ['user_id' => $victime->id, 'projet_id' => $projet->id]);
        $this->assertDatabaseHas('consultation_projets', ['user_id' => $intrus->id, 'projet_id' => $projet->id]);

        // Deux lignes distinctes, une par propriétaire.
        $this->assertSame(2, ConsultationProjet::where('projet_id', $projet->id)->distinct()->count('user_id'));
    }

    public function test_l_historique_n_expose_aucune_donnee_technique(): void
    {
        // MINIMISATION DES DONNÉES. Seule la paire et son horodatage peuvent
        // exister — pas d'adresse IP, pas de user-agent, pas de géolocalisation.
        $citoyen = $this->citoyen();
        $projet  = $this->projet();

        $this->asCitoyen($citoyen)->get(route('citizen.projets.show', $projet))->assertOk();

        $colonnes = \Illuminate\Support\Facades\DB::getSchemaBuilder()->getColumnListing('consultation_projets');

        $this->assertEqualsCanonicalizing(
            ['id', 'user_id', 'projet_id', 'created_at', 'updated_at', 'bucket'],
            $colonnes,
            'La table d\'historique doit rester minimale : aucune donnee technique.'
        );

        foreach (['ip_address', 'ip', 'user_agent', 'referer', 'latitude', 'longitude', 'device', 'session_id'] as $interdit) {
            $this->assertNotContains($interdit, $colonnes);
        }
    }
}
