<?php

namespace Tests\Feature;

use App\Models\Financement;
use App\Models\Projet;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

/**
 * Export du rapport financier depuis /manager/budget.
 *
 * Le point central est la cohérence : ce que le gestionnaire télécharge doit
 * être exactement ce qu'il voit à l'écran, puisque les deux lisent le même
 * BudgetReportService.
 */
class ExportRapportBudgetTest extends TestCase
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

    private function manager()
    {
        return $this->createUserWithRole(Role::MANAGER);
    }

    /** CSV téléchargé, prêt à être analysé. */
    private function csv($response): string
    {
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        return $response->streamedContent();
    }

    /**
     * Découpe le CSV en lignes de champs, en conservant les lignes vides
     * (`null`) qui séparent les sections.
     *
     * Analyser des champs plutôt que des chaînes évite de dépendre du
     * comportement de fputcsv (qui encadre les valeurs contenant des espaces).
     *
     * @return array<int, array<int, string>|null>
     */
    private function csvRows(string $csv): array
    {
        $csv  = preg_replace('/^\xEF\xBB\xBF/', '', $csv);
        $rows = [];

        foreach (explode("\n", $csv) as $ligne) {
            $ligne = rtrim($ligne, "\r");
            $rows[] = $ligne === '' ? null : str_getcsv($ligne, ';');
        }

        return $rows;
    }

    /**
     * Première ligne dont la première colonne vaut exactement $cle.
     *
     * @param  array<int, array<int, string>|null>  $rows
     * @return array<int, string>
     */
    private function ligne(array $rows, string $cle): array
    {
        foreach ($rows as $row) {
            if ($row !== null && ($row[0] ?? null) === $cle) {
                return $row;
            }
        }

        $this->fail("Aucune ligne « {$cle} » dans le rapport CSV exporté.");
    }

    /** Nombre de lignes de données d'une section (en-tête non compris). */
    private function compteSection(array $rows, string $prefixe): int
    {
        $debut = null;

        foreach ($rows as $i => $row) {
            if ($row !== null && str_starts_with((string) $row[0], $prefixe)) {
                $debut = $i;
                break;
            }
        }

        $this->assertNotNull($debut, "Section « {$prefixe} » absente du rapport.");

        $compte = 0;

        for ($i = $debut + 1; $i < count($rows); $i++) {
            if ($rows[$i] === null) {
                break;                       // fin de section
            }
            if ($i === $debut + 1) {
                continue;                    // en-tête de colonnes
            }
            $compte++;
        }

        return $compte;
    }

    /* ── Sécurité : on ne touche jamais à la base de développement ───────── */

    public function test_la_suite_ne_tourne_jamais_sur_la_base_de_developpement(): void
    {
        $connexion = config('database.default');
        $base      = config("database.connections.{$connexion}.database");

        $this->assertNotSame(
            'aquasecure',
            $base,
            "La suite de tests pointe sur la base de développement '{$base}' : RefreshDatabase la viderait."
        );
    }

    /* ── 1. Contrôle d'accès ─────────────────────────────────────────────── */

    public function test_un_visiteur_ne_peut_pas_exporter_le_rapport(): void
    {
        $this->get('/manager/budget/export/csv')->assertRedirect(route('auth.login'));
        $this->get('/manager/budget/export/pdf')->assertRedirect(route('auth.login'));
    }

    public function test_un_citoyen_ne_peut_pas_exporter_le_rapport(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);

        $this->actingAs($citoyen)->get('/manager/budget/export/csv')->assertForbidden();
        $this->actingAs($citoyen)->get('/manager/budget/export/pdf')->assertForbidden();
    }

    public function test_un_technicien_ne_peut_pas_exporter_le_rapport(): void
    {
        $technicien = $this->createUserWithRole(Role::TECHNICIEN);

        $this->actingAs($technicien)->get('/manager/budget/export/csv')->assertForbidden();
    }

    /* ── 2. CSV ──────────────────────────────────────────────────────────── */

    public function test_le_csv_se_telecharge_avec_le_bon_nom_et_le_bon_encodage(): void
    {
        Financement::factory()->create();

        $response = $this->actingAs($this->manager())
            ->get('/manager/budget/export/csv');

        $response->assertDownload('rapport-budget-' . now()->format('Y-m-d') . '.csv');

        $contenu = $this->csv($response);

        // BOM UTF-8 obligatoire : sinon Excel affiche les accents cassés.
        $this->assertSame("\xEF\xBB\xBF", substr($contenu, 0, 3));
        $this->assertStringContainsString('Rapport financier et budgétaire', $contenu);
    }

    public function test_le_csv_contient_les_quatre_sections_du_rapport(): void
    {
        Financement::factory()->create();

        $contenu = $this->csv(
            $this->actingAs($this->manager())->get('/manager/budget/export/csv')
        );

        $this->assertStringContainsString('SYNTHÈSE', $contenu);
        $this->assertStringContainsString('FINANCEMENTS (', $contenu);
        $this->assertStringContainsString('SITUATION PAR PROJET (', $contenu);
        $this->assertStringContainsString('RÉPARTITION PAR SOURCE DE FINANCEMENT (', $contenu);

        // En-têtes du tableau de détail, identiques à l'écran.
        foreach (['Date', 'Projet', 'Source de financement', 'Montant (DT)', 'Budget projet (DT)', '% Financé du projet'] as $entete) {
            $this->assertStringContainsString($entete, $contenu);
        }
    }

    public function test_les_indicateurs_du_csv_sont_ceux_reellement_calcules(): void
    {
        $p1 = Projet::factory()->create(['statut' => 'en_cours', 'budget' => 500000]);
        $p2 = Projet::factory()->create(['statut' => 'planifie',  'budget' => 300000]);

        Financement::factory()->create(['projet_id' => $p1->id, 'montant' => 120000, 'source' => 'Banque Mondiale']);
        Financement::factory()->create(['projet_id' => $p1->id, 'montant' =>  80000, 'source' => 'Banque Mondiale']);
        Financement::factory()->create(['projet_id' => $p2->id, 'montant' =>  50000, 'source' => 'Union Européenne']);

        $contenu = $this->csv(
            $this->actingAs($this->manager())->get('/manager/budget/export/csv')
        );

        $rows = $this->csvRows($contenu);

        $budgetTotal = (float) Projet::sum('budget');
        $finance     = (float) Financement::sum('montant');

        // Écriture française : pas de séparateur de millier, décimale par
        // virgule — ce sont des nombres lisibles par Excel FR, pas des textes.
        $this->assertSame(
            ['Budget total des projets (DT)', number_format($budgetTotal, 2, ',', '')],
            $this->ligne($rows, 'Budget total des projets (DT)')
        );
        $this->assertSame(
            ['Total financé (DT)', number_format($finance, 2, ',', '')],
            $this->ligne($rows, 'Total financé (DT)')
        );
        $this->assertSame(
            ['Budget restant (DT)', number_format($budgetTotal - $finance, 2, ',', '')],
            $this->ligne($rows, 'Budget restant (DT)')
        );
        $this->assertSame(
            ['Taux de financement (%)', number_format($budgetTotal > 0 ? $finance / $budgetTotal * 100 : 0, 1, ',', '')],
            $this->ligne($rows, 'Taux de financement (%)')
        );
        $this->assertSame(['Nombre de projets', '2'], $this->ligne($rows, 'Nombre de projets'));
        $this->assertSame(['Nombre de financements', '3'], $this->ligne($rows, 'Nombre de financements'));
        $this->assertSame(['Sources de financement distinctes', '2'], $this->ligne($rows, 'Sources de financement distinctes'));

        // Chaque opération figure bien avec sa source et son montant.
        $this->assertSame(
            ['Banque Mondiale', '2', number_format(200000, 2, ',', ''), '80,0'],
            $this->ligne($rows, 'Banque Mondiale')
        );
        $this->assertSame(
            ['Union Européenne', '1', number_format(50000, 2, ',', ''), '20,0'],
            $this->ligne($rows, 'Union Européenne')
        );
    }

    public function test_le_csv_liste_une_ligne_par_financement_et_termine_par_une_somme(): void
    {
        $projet = Projet::factory()->create(['budget' => 100000]);

        Financement::factory()->create(['projet_id' => $projet->id, 'montant' => 40000]);
        Financement::factory()->create(['projet_id' => $projet->id, 'montant' => 15000]);

        $contenu = $this->csv(
            $this->actingAs($this->manager())->get('/manager/budget/export/csv')
        );

        $this->assertStringContainsString('FINANCEMENTS (2)', $contenu);
        $this->assertStringContainsString('TOTAL;;;' . number_format(55000, 2, ',', ''), $contenu);
    }

    public function test_le_csv_contient_les_projets_sans_financement(): void
    {
        Projet::factory()->create([
            'nom'    => 'Projet jamais financé à Testouille',
            'statut' => 'planifie',
            'budget' => 77000,
        ]);

        $contenu = $this->csv(
            $this->actingAs($this->manager())->get('/manager/budget/export/csv')
        );

        $rows = $this->csvRows($contenu);

        // Présent dans la synthèse, avec un financement nul : le rapport ne
        // masque pas les projets qui n'ont encore rien reçu.
        $this->assertSame(
            ['Projet jamais financé à Testouille', 'planifie', '77000,00', '0,00', '77000,00', '0,0', '0'],
            $this->ligne($rows, 'Projet jamais financé à Testouille')
        );
        $this->assertSame(['Nombre de projets', '1'], $this->ligne($rows, 'Nombre de projets'));
        $this->assertSame(['Nombre de financements', '0'], $this->ligne($rows, 'Nombre de financements'));
    }

    /* ── 3. PDF ──────────────────────────────────────────────────────────── */

    public function test_le_rapport_pdf_se_telecharge_et_est_un_vrai_pdf(): void
    {
        Projet::factory()->create(['statut' => 'en_cours', 'budget' => 120000]);
        Financement::factory()->create(['montant' => 30000, 'source' => 'Banque Mondiale']);

        $response = $this->actingAs($this->manager())
            ->get('/manager/budget/export/pdf');

        $response->assertOk();
        $response->assertDownload('rapport-budget-' . now()->format('Y-m-d') . '.pdf');
        $response->assertHeader('Content-Type', 'application/pdf');

        // dompdf renvoie le contenu dans une Response standard (pas un
        // BinaryFileResponse) : le corps se lit directement.
        $contenu = $response->getContent();

        $this->assertStringStartsWith('%PDF', $contenu, 'Le fichier généré n\'est pas un PDF.');
        $this->assertStringEndsWith('%%EOF', trim($contenu), 'Troncature : le PDF n\'est pas terminé.');
        $this->assertGreaterThan(1000, strlen($contenu), 'Le PDF semble vide.');
    }

    /**
     * dompdf compresse les flux : on ne peut pas chercher le texte brut dans
     * les octets du PDF. On vérifie donc le gabarit lui-même, qui est la
     * vraie source du document.
     */
    public function test_le_gabarit_du_rapport_pdf_contient_toutes_les_sections(): void
    {
        $projet = Projet::factory()->create([
            'nom'    => 'Réhabilitation du réseau de Bizerte',
            'statut' => 'en_cours',
            'budget' => 200000,
        ]);
        Projet::factory()->create(['nom' => 'Projet sans le sou', 'statut' => 'planifie', 'budget' => 50000]);

        Financement::factory()->create([
            'projet_id' => $projet->id, 'montant' => 60000, 'source' => 'Banque Mondiale',
        ]);

        $service = app(\App\Services\BudgetReportService::class);
        $stats   = $service->stats();

        $html = view('manager.exports.budget-pdf', [
            'stats'       => $stats,
            'parProjet'   => $service->syntheseParProjet($stats),
            'parSource'   => $service->syntheseParSource($stats),
            'emetteur'    => 'Ines Mansouri',
            'generatedAt' => now()->format('d/m/Y à H:i'),
        ])->render();

        // Titre et en-tête.
        $this->assertStringContainsString('AQUASECURE', $html);
        $this->assertStringContainsString('RAPPORT FINANCIER ET BUDGÉTAIRE', $html);
        $this->assertStringContainsString('Ines Mansouri', $html);

        // Les 4 sections, dans l'ordre du document.
        foreach ([
            'Indicateurs clés',
            'Situation budgétaire par projet',
            'Répartition par source de financement',
            'Détail des opérations de financement',
        ] as $section) {
            $this->assertStringContainsString($section, $html);
        }

        // Chiffres réels.
        $this->assertStringContainsString(number_format((float) Financement::sum('montant'), 0, ',', ' ') . ' DT', $html);
        $this->assertStringContainsString('Banque Mondiale', $html);
        $this->assertStringContainsString('Réhabilitation du réseau de Bizerte', $html);
        $this->assertStringContainsString('Projet sans le sou', $html);
        $this->assertStringContainsString('TOTAL GÉNÉRAL', $html);

        // Les libellés de statut du dépôt, pas des statuts inventés.
        $this->assertStringContainsString('En cours', $html);
        $this->assertStringContainsString('Planifié', $html);
    }

    /* ── 4. Cohérence écran ⇄ export ─────────────────────────────────────── */

    public function test_la_page_budget_affiche_exactement_les_memes_totaux_que_l_export(): void
    {
        $projet = Projet::factory()->create(['budget' => 250000]);
        Financement::factory()->create(['projet_id' => $projet->id, 'montant' => 90000]);

        $page = $this->actingAs($this->manager())->get('/manager/budget');
        $page->assertOk();

        $finance  = (float) Financement::sum('montant');
        $budget   = (float) Projet::sum('budget');

        // La page lit le service…
        $page->assertViewHas('totalFinance', $finance);
        $page->assertViewHas('totalBudget', $budget);
        $page->assertViewHas('budgetRestant', $budget - $finance);
        $page->assertViewHas('sourcesUniques', Financement::pluck('source')->unique()->count());

        // …et l'export, le même service, contient les mêmes chiffres.
        $csv = $this->csv(
            $this->actingAs($this->manager())->get('/manager/budget/export/csv')
        );

        $this->assertStringContainsString(number_format($finance, 2, ',', ''), $csv);
        $this->assertStringContainsString(number_format($budget - $finance, 2, ',', ''), $csv);
    }

    public function test_les_liens_d_export_sont_presents_sur_la_page_budget(): void
    {
        $this->actingAs($this->manager())
            ->get('/manager/budget')
            ->assertOk()
            ->assertSee(route('manager.budget.export.pdf'), false)
            ->assertSee(route('manager.budget.export.csv'), false)
            ->assertSee('Exporter le rapport (PDF / CSV)');
    }

    public function test_le_rapport_reflete_les_donnees_d_apres_suppression(): void
    {
        // Une demande acceptée mais jamais confirmée ne doit pas apparaître :
        // seuls les Financement réellement enregistrés sont exportés.
        $projet = Projet::factory()->create(['budget' => 400000]);
        Financement::factory()->create(['projet_id' => $projet->id, 'montant' => 25000]);

        $contenu = $this->csv(
            $this->actingAs($this->manager())->get('/manager/budget/export/csv')
        );

        $this->assertStringContainsString('FINANCEMENTS (1)', $contenu);
        $this->assertSame(
            1,
            substr_count($contenu, 'SITUATION PAR PROJET (' . Projet::count() . ')')
        );
    }
}
