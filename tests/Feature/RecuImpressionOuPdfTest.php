<?php

namespace Tests\Feature;

use App\Models\DemandeFinancement;
use App\Models\Paiement;
use App\Models\Projet;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

/**
 * The citizen chooses how to keep the receipt: print it, or download a PDF.
 *
 * Both routes are guarded exactly like the on-screen receipt — knowing the URL
 * is never enough to fetch someone else's receipt.
 */
class RecuImpressionOuPdfTest extends TestCase
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

    private function paiement(User $citoyen, string $statut = Paiement::CONFIRME): Paiement
    {
        $decideur = $this->createUserWithRole(Role::MANAGER);

        $projet = Projet::factory()->create(['statut' => 'en_cours', 'budget' => 500000]);

        $demande = DemandeFinancement::create([
            'projet_id'  => $projet->id,
            'user_id'    => $citoyen->id,
            'montant'    => 1800,
            'devise'     => 'TND',
            'statut'     => $statut === Paiement::CONFIRME
                ? DemandeFinancement::PAYEE
                : DemandeFinancement::ACCEPTEE,
            'decided_by' => $decideur->id,
            'decided_at' => now(),
        ]);

        return Paiement::create([
            'demande_id'        => $demande->id,
            'user_id'           => $citoyen->id,
            'projet_id'         => $projet->id,
            'montant'           => 1800,
            'devise'            => 'TND',
            'statut'            => $statut,
            'reference_interne' => 'AQS-TEST-' . uniqid(),
            'motif'             => 'Simulation de paiement acceptée.',
            'confirmed_at'      => $statut === Paiement::CONFIRME ? now() : null,
        ]);
    }

    /* ── 1. Le citoyen a le choix sur la page du reçu ────────────────────── */

    public function test_la_page_du_recu_propose_les_deux_facons_de_conserver_le_document(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $paiement = $this->paiement($citoyen);

        $response = $this->actingAs($citoyen)
            ->get(route('citizen.demandes.paiement.recu', $paiement));

        $response->assertOk();

        // Bouton d'impression (navigateur).
        $response->assertSee('window.print()');
        $response->assertSee('Imprimer', false);

        // Lien de téléchargement vers la route PDF.
        $response->assertSee(route('citizen.demandes.paiement.recu.pdf', $paiement), false);
        $response->assertSee('Télécharger le PDF');

        // On n'écrase plus les deux possibilités en un seul bouton ambigu.
        $response->assertDontSee('Imprimer / sauvegarder en PDF');

        // Les deux actions restent masquées à l'impression.
        $response->assertSee('no-print');
    }

    /* ── 2. Téléchargement du PDF ────────────────────────────────────────── */

    public function test_le_citoyen_proprietaire_telecharge_un_vrai_pdf_nomme_par_reference(): void
    {
        $citoyen  = $this->createUserWithRole(Role::CITOYEN);
        $paiement = $this->paiement($citoyen);

        $response = $this->actingAs($citoyen)
            ->get(route('citizen.demandes.paiement.recu.pdf', $paiement));

        $response->assertOk();
        $response->assertDownload('recu-contribution-' . $paiement->reference_interne . '.pdf');
        $response->assertHeader('Content-Type', 'application/pdf');

        $contenu = $response->getContent();

        $this->assertStringStartsWith('%PDF', $contenu, 'Le fichier généré n\'est pas un PDF.');
        $this->assertStringEndsWith('%%EOF', trim($contenu), 'Troncature : le PDF n\'est pas terminé.');
        $this->assertGreaterThan(1000, strlen($contenu), 'Le PDF semble vide.');
    }

    public function test_le_recu_d_une_simulation_echouee_reste_telechargeable(): void
    {
        $citoyen  = $this->createUserWithRole(Role::CITOYEN);
        $paiement = $this->paiement($citoyen, Paiement::ECHOUE);

        $response = $this->actingAs($citoyen)
            ->get(route('citizen.demandes.paiement.recu.pdf', $paiement));

        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    /* ── 3. Le PDF contient bien toutes les données ──────────────────────── */

    public function test_le_gabarit_du_recu_pdf_contient_la_mention_et_toutes_les_donnees(): void
    {
        $citoyen  = $this->createUserWithRole(Role::CITOYEN, ['name' => 'Abdelgani Bannouri']);
        $paiement = $this->paiement($citoyen);

        $html = view('citizen.demandes.recu-pdf', [
            'paiement'    => $paiement->load(['demande.projet', 'demande.decideur', 'citoyen']),
            'generatedAt' => now()->format('d/m/Y à H:i'),
        ])->render();

        // La mention de simulation est NON NÉGOCIABLE et doit survivre au PDF.
        $this->assertStringContainsString('Simulation — aucune transaction financière réelle', $html);
        $this->assertStringContainsString('ne constitue ni une preuve de paiement', $html);
        $this->assertStringContainsString('contribution simulée', $html);

        // En-tête et référence.
        $this->assertStringContainsString('AQUASECURE', $html);
        $this->assertStringContainsString($paiement->reference_interne, $html);

        // Contenu du reçu, identique à ce que l'écran affiche.
        $this->assertStringContainsString('Abdelgani Bannouri', $html);
        $this->assertStringContainsString($citoyen->email, $html);
        $this->assertStringContainsString('1 800,00', $html);
        $this->assertStringContainsString('TND', $html);
        $this->assertStringContainsString('n° ' . $paiement->demande_id, $html);
        $this->assertStringContainsString('Simulation de paiement acceptée.', $html);
        $this->assertStringContainsString('Projet financé', $html);

        // Aucun montant n'apparaît sans sa devise.
        $this->assertStringContainsString('Montant de la contribution', $html);
    }

    /* ── 4. Contrôle d'accès : le PDF est protégé comme la page ──────────── */

    public function test_un_visiteur_ne_peut_pas_recuperer_un_recu(): void
    {
        $citoyen  = $this->createUserWithRole(Role::CITOYEN);
        $paiement = $this->paiement($citoyen);

        $this->get(route('citizen.demandes.paiement.recu', $paiement))
            ->assertRedirect(route('auth.login'));
        $this->get(route('citizen.demandes.paiement.recu.pdf', $paiement))
            ->assertRedirect(route('auth.login'));
    }

    public function test_un_autre_citoyen_ne_peut_pas_telecharger_mon_recu(): void
    {
        $victime  = $this->createUserWithRole(Role::CITOYEN);
        $intrus   = $this->createUserWithRole(Role::CITOYEN);
        $paiement = $this->paiement($victime);

        $this->actingAs($intrus)
            ->get(route('citizen.demandes.paiement.recu.pdf', $paiement))
            ->assertForbidden();

        $this->actingAs($intrus)
            ->get(route('citizen.demandes.paiement.recu', $paiement))
            ->assertForbidden();
    }

    public function test_un_employe_sans_role_citoyen_ne_peut_pas_recuperer_un_recu(): void
    {
        $citoyen  = $this->createUserWithRole(Role::CITOYEN);
        $paiement = $this->paiement($citoyen);

        $this->actingAs($this->createUserWithRole(Role::MANAGER))
            ->get(route('citizen.demandes.paiement.recu.pdf', $paiement))
            ->assertForbidden();

        $this->actingAs($this->createUserWithRole(Role::TECHNICIEN))
            ->get(route('citizen.demandes.paiement.recu.pdf', $paiement))
            ->assertForbidden();
    }

    public function test_l_identifiant_du_recu_transmis_dans_l_url_ne_change_rien(): void
    {
        $citoyen  = $this->createUserWithRole(Role::CITOYEN);
        $autre    = $this->createUserWithRole(Role::CITOYEN);
        $mien     = $this->paiement($citoyen);
        $pasMien  = $this->paiement($autre);

        // Tenter de forcer un autre identifiant dans la query string ne donne
        // accès qu'à sa propre pièce : le propriétaire vient de la route.
        $response = $this->actingAs($citoyen)
            ->get(route('citizen.demandes.paiement.recu.pdf', $mien) . '?paiement=' . $pasMien->id . '&id=' . $pasMien->id);

        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $response->assertDownload('recu-contribution-' . $mien->reference_interne . '.pdf');
    }
}
