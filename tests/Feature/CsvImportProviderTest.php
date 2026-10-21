<?php

namespace Tests\Feature;

use App\Models\Infrastructure;
use App\Models\MesureQualite;
use App\Models\PointMesure;
use App\Models\Zone;
use App\Services\ExternalData\CsvImportProvider;
use App\Services\ExternalData\ExternalSyncService;
use App\Services\ExternalData\NormalisedObservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Tests du fournisseur d'import manuel CSV/XLSX : détection du séparateur,
 * normalisation des en-têtes, conversion des valeurs, dates et rattachement
 * des mesures aux points / infrastructures / zones.
 */
class CsvImportProviderTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<string> Fichiers temporaires à supprimer après chaque test. */
    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            @unlink($file);
        }

        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // Outils
    // -------------------------------------------------------------------------

    private function tempFile(string $content, string $extension = 'csv'): string
    {
        $path = sys_get_temp_dir() . '/aqua_import_' . uniqid('', true) . '.' . $extension;
        file_put_contents($path, $content);
        $this->tempFiles[] = $path;

        return $path;
    }

    /** @return list<NormalisedObservation> */
    private function parse(string $content, string $extension = 'csv', string $source = 'import_test'): array
    {
        return (new CsvImportProvider())
            ->setFilePath($this->tempFile($content, $extension), $source)
            ->fetchObservations();
    }

    /**
     * Construit un classeur XLSX minimal (sans PhpSpreadsheet) avec ZipArchive.
     *
     * @param  array<string, string>  $entries  chemin dans l'archive => contenu XML
     */
    private function xlsxFile(array $entries): string
    {
        if (! class_exists(\ZipArchive::class)) {
            $this->markTestSkipped('Extension zip indisponible dans cet environnement.');
        }

        $path = sys_get_temp_dir() . '/aqua_import_' . uniqid('', true) . '.xlsx';
        $zip = new \ZipArchive();
        $zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        foreach ($entries as $name => $xml) {
            $zip->addFromString($name, $xml);
        }
        $zip->close();
        $this->tempFiles[] = $path;

        return $path;
    }

    private function observation(array $overrides = [], array $rawMeta = []): NormalisedObservation
    {
        $args = array_merge([
            'externalId' => 'csv-test',
            'provider' => 'csv',
            'stationCode' => 'PT-TEST',
            'stationName' => 'Station test',
            'latitude' => 0.0,
            'longitude' => 0.0,
            'observationDate' => new \DateTimeImmutable('2026-01-01 08:00:00'),
            'ph' => 7.0,
            'turbidite' => null,
            'nitrates' => null,
            'chloreResiduel' => null,
            'plomb' => null,
            'qualityFlag' => null,
            'rawMeta' => $rawMeta,
        ], $overrides);

        return new NormalisedObservation(...$args);
    }

    // -------------------------------------------------------------------------
    // Fichier absent / métadonnées du fournisseur
    // -------------------------------------------------------------------------

    public function test_provider_exposes_its_slug_and_name(): void
    {
        $provider = new CsvImportProvider();

        $this->assertSame('csv', $provider->getProviderSlug());
        $this->assertSame('Import manuel CSV/XLSX', $provider->getProviderName());
    }

    public function test_returns_no_observation_without_file_or_when_file_is_missing(): void
    {
        $provider = new CsvImportProvider();
        $this->assertSame([], $provider->fetchObservations());

        $provider->setFilePath(sys_get_temp_dir() . '/fichier-inexistant-' . uniqid() . '.csv');
        $this->assertSame([], $provider->fetchObservations());
    }

    // -------------------------------------------------------------------------
    // Lecture CSV
    // -------------------------------------------------------------------------

    public function test_semicolon_csv_with_bom_accents_and_decimal_commas_is_normalised(): void
    {
        $csv = "\xEF\xBB\xBF"
            . "Code;Nom;Zone;Date mesure;pH;Turbidité (NTU);Chlore résiduel (mg/L);Plomb (µg/L);Nitrates;Latitude;Longitude;\n"
            . "PT-001;Station Nord;Zone Nord;18/10/2026 09:30;7,2;0,4;ND;1 234,5;< 50;36,8;10,1\n"
            . "\n"
            . ";;;;;;;;;;\n"
            . "PT-002;Sans mesure;Zone Sud;18/10/2026;n/a;-;;;;;\n"
            . "PT-003;Ligne courte;Zone Sud;19/10/2026;8.1\n";

        $observations = $this->parse($csv, 'csv', 'labo_regional');

        // La ligne sans aucune valeur mesurée est ignorée, la ligne courte est complétée.
        $this->assertCount(2, $observations);

        [$first, $short] = $observations;
        $this->assertSame('csv', $first->provider);
        $this->assertSame('PT-001', $first->stationCode);
        $this->assertSame('Station Nord', $first->stationName);
        $this->assertSame(7.2, $first->ph);
        $this->assertSame(0.4, $first->turbidite);
        $this->assertNull($first->chloreResiduel, 'ND doit rester null et non 0');
        $this->assertSame(1234.5, $first->plomb);
        $this->assertSame(50.0, $first->nitrates);
        $this->assertSame(36.8, $first->latitude);
        $this->assertSame(10.1, $first->longitude);
        $this->assertSame('2026-10-18 09:30', $first->observationDate->format('Y-m-d H:i'));
        $this->assertSame('Zone Nord', $first->rawMeta['zone']);
        $this->assertSame('labo_regional', $first->rawMeta['source_name']);
        $this->assertSame('manuel', $first->rawMeta['source_donnee']);
        $this->assertStringStartsWith('csv-', $first->externalId);

        $this->assertSame('PT-003', $short->stationCode);
        $this->assertSame(8.1, $short->ph);
        $this->assertNull($short->turbidite);
        $this->assertSame(0.0, $short->latitude);
    }

    public static function delimiterProvider(): array
    {
        return [
            'virgule' => [','],
            'tabulation' => ["\t"],
            'barre verticale' => ['|'],
        ];
    }

    #[DataProvider('delimiterProvider')]
    public function test_delimiter_is_detected_from_the_header_line(string $delimiter): void
    {
        $csv = implode($delimiter, ['code', 'ph', 'nitrates']) . "\n"
            . implode($delimiter, ['PT-D', '6.9', '12']) . "\n";

        $observations = $this->parse($csv);

        $this->assertCount(1, $observations);
        $this->assertSame('PT-D', $observations[0]->stationCode);
        $this->assertSame(6.9, $observations[0]->ph);
        $this->assertSame(12.0, $observations[0]->nitrates);
    }

    public function test_single_column_file_falls_back_to_comma_and_generates_a_station_code(): void
    {
        $observations = $this->parse("ph\n7.4\n");

        $this->assertCount(1, $observations);
        // Aucun code ni nom : un code d'import est généré à partir du numéro de ligne.
        $this->assertSame('PT-IMP-2', $observations[0]->stationCode);
        $this->assertSame('PT-IMP-2', $observations[0]->stationName);
        $this->assertSame(7.4, $observations[0]->ph);
    }

    public function test_external_id_is_stable_for_the_same_file_and_depends_on_the_source(): void
    {
        $csv = "code,date_mesure,ph\nPT-1,2026-01-05 10:00:00,7.1\n";

        $first = $this->parse($csv, 'csv', 'source_a')[0];
        $again = $this->parse($csv, 'csv', 'source_a')[0];
        $other = $this->parse($csv, 'csv', 'source_b')[0];

        $this->assertSame($first->externalId, $again->externalId);
        $this->assertNotSame($first->externalId, $other->externalId);
    }

    // -------------------------------------------------------------------------
    // Déduction du code et du nom de station
    // -------------------------------------------------------------------------

    public function test_station_code_and_name_are_derived_from_a_demo_reference(): void
    {
        $csv = "reference,ph\nDEMO-PUITS-NORD-01,7.0\nREF_reservoir_2,7.1\nMES-Station-Est-9,7.2\n";

        [$demo, $ref, $station] = $this->parse($csv);

        $this->assertSame('PT-PUITS-NORD', $demo->stationCode);
        $this->assertSame('Station Puits Nord', $demo->stationName);
        // Les références de démonstration sont réécrites en référence de mesure.
        $this->assertSame('MES-PUITS-NORD-01', $demo->rawMeta['custom_reference']);

        $this->assertSame('PT-RESERVOIR', $ref->stationCode);
        $this->assertSame('Station Reservoir', $ref->stationName);
        $this->assertSame('REF_reservoir_2', $ref->rawMeta['custom_reference']);

        // Un nom dérivé commençant déjà par « Station » n'est pas préfixé deux fois.
        $this->assertSame('Station Est', $station->stationName);
    }

    public function test_station_code_falls_back_to_point_id_then_infrastructure(): void
    {
        $csv = "point_mesure_id,infra,infrastructure_id,ph\n"
            . "7,,,7.0\n"
            . ",INF-AB-1234,,7.1\n"
            . ",,42,7.2\n";

        [$byPoint, $byInfraRef, $byInfraId] = $this->parse($csv);

        $this->assertSame('PT-007', $byPoint->stationCode);
        $this->assertSame(7, $byPoint->rawMeta['point_mesure_id']);

        $this->assertSame('INF-AB-1234', $byInfraRef->stationCode);
        $this->assertSame('INF-AB-1234', $byInfraRef->rawMeta['infrastructure_reference']);

        $this->assertSame('INF-42', $byInfraId->stationCode);
        $this->assertSame('42', $byInfraId->rawMeta['infrastructure_id']);
        $this->assertSame('INF-42', $byInfraId->stationName);
    }

    public function test_infrastructure_reference_column_is_not_used_as_measurement_reference(): void
    {
        $observation = $this->parse("infrastructure_reference,ph\nINF-AB-1234,7.1\n")[0];

        $this->assertSame('INF-AB-1234', $observation->stationCode);
        $this->assertNull($observation->rawMeta['custom_reference']);
    }

    public function test_partial_header_match_ignores_foreign_key_columns(): void
    {
        // « ph_labo » est reconnu par correspondance partielle ; « station_id »
        // ne doit pas être pris pour le nom de la station (colonne *_id).
        $csv = "station_id,ph_labo,gouvernorat\n42,7.3,Sfax\n";

        $observation = $this->parse($csv)[0];

        $this->assertSame(7.3, $observation->ph);
        $this->assertNotSame('42', $observation->stationName);
        $this->assertSame('PT-IMP-2', $observation->stationCode);
        $this->assertSame('Sfax', $observation->rawMeta['zone']);
    }

    // -------------------------------------------------------------------------
    // Dates
    // -------------------------------------------------------------------------

    public static function dateProvider(): array
    {
        return [
            'jour/mois/année heure' => ['18/10/2026 09:30:15', '2026-10-18 09:30'],
            'iso avec minutes' => ['2026-10-18 09:30', '2026-10-18 09:30'],
            'jour-mois-année' => ['18-10-2026', '2026-10-18'],
            'numéro de série Excel' => ['45292', '2024-01-01'],
            'ISO 8601 complet' => ['2026-10-18T09:30:00+00:00', '2026-10-18 09:30'],
        ];
    }

    #[DataProvider('dateProvider')]
    public function test_measurement_dates_are_parsed_from_common_formats(string $raw, string $expected): void
    {
        $observation = $this->parse("code;date;ph\nPT-DATE;{$raw};7\n")[0];

        $format = strlen($expected) === 10 ? 'Y-m-d' : 'Y-m-d H:i';
        $this->assertSame($expected, $observation->observationDate->format($format));
    }

    public function test_unparseable_date_falls_back_to_the_import_time(): void
    {
        $observation = $this->parse("code;date;ph\nPT-DATE;pas une date;7\n")[0];

        $this->assertEqualsWithDelta(time(), $observation->observationDate->getTimestamp(), 60);
    }

    public function test_two_digit_year_is_parsed_in_the_current_century(): void
    {
        $observation = $this->parse("code;date;ph\nPT-Y2;18/10/26;7\n")[0];

        $this->assertSame('2026-10-18', $observation->observationDate->format('Y-m-d'));
    }

    public function test_us_formatted_date_is_parsed_as_month_day_year(): void
    {
        $observation = $this->parse("code;date;ph\nPT-US;12/31/2025;7\n")[0];

        $this->assertSame('2025-12-31', $observation->observationDate->format('Y-m-d'));
    }

    // -------------------------------------------------------------------------
    // XLSX (lecteur ZipArchive intégré)
    // -------------------------------------------------------------------------

    public function test_xlsx_with_shared_inline_and_rich_text_strings_is_parsed(): void
    {
        $sharedStrings = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<si><t>code</t></si>'
            . '<si><r><t>no</t></r><r><t>m</t></r></si>'
            . '<si><t>pH</t></si>'
            . '<si><t>PT-X1</t></si>'
            . '<si></si>'
            . '</sst>';
        $sheet = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'
            . '<row r="1"><c r="A1" t="s"><v>0</v></c><c r="B1" t="s"><v>1</v></c><c r="C1" t="s"><v>2</v></c><c r="D1" t="inlineStr"><is><t>Nitrates</t></is></c></row>'
            . '<row r="2"><c r="A2" t="s"><v>3</v></c><c r="B2" t="inlineStr"><is><t>Forage Sud</t></is></c><c r="C2"><v>7.6</v></c><c r="D2"><v>21</v></c></row>'
            . '<row r="3"><c r="A3" t="s"><v>4</v></c></row>'
            . '<row r="4"><c r="A4" t="inlineStr"><is><t>PT-X2</t></is></c><c r="C4"><v>6.8</v></c></row>'
            . '</sheetData></worksheet>';

        $path = $this->xlsxFile([
            'xl/sharedStrings.xml' => $sharedStrings,
            'xl/worksheets/sheet1.xml' => $sheet,
        ]);

        $observations = (new CsvImportProvider())->setFilePath($path)->fetchObservations();

        $this->assertCount(2, $observations);
        $this->assertSame('PT-X1', $observations[0]->stationCode);
        $this->assertSame('Forage Sud', $observations[0]->stationName);
        $this->assertSame(7.6, $observations[0]->ph);
        $this->assertSame(21.0, $observations[0]->nitrates);
        // Ligne avec une cellule manquante (colonne B) : complétée par une chaîne vide.
        $this->assertSame('PT-X2', $observations[1]->stationCode);
        $this->assertSame(6.8, $observations[1]->ph);
        $this->assertNull($observations[1]->nitrates);
    }

    public function test_xlsx_reads_the_first_available_worksheet_when_sheet1_is_missing(): void
    {
        $sheet = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'
            . '<row r="1"><c r="A1" t="inlineStr"><is><t>code</t></is></c><c r="AA1" t="inlineStr"><is><t>turbidite</t></is></c></row>'
            . '<row r="2"><c r="A2" t="inlineStr"><is><t>PT-AA</t></is></c><c r="AA2"><v>1.5</v></c></row>'
            . '</sheetData></worksheet>';

        $path = $this->xlsxFile([
            'docProps/app.xml' => '<Properties/>',
            'xl/worksheets/sheet3.xml' => $sheet,
        ]);

        $observations = (new CsvImportProvider())->setFilePath($path)->fetchObservations();

        // La colonne AA (27e) est correctement positionnée malgré les colonnes vides.
        $this->assertCount(1, $observations);
        $this->assertSame('PT-AA', $observations[0]->stationCode);
        $this->assertSame(1.5, $observations[0]->turbidite);
    }

    public function test_xlsx_without_usable_rows_or_worksheet_returns_nothing(): void
    {
        $headerOnly = $this->xlsxFile([
            'xl/worksheets/sheet1.xml' => '<worksheet><sheetData><row r="1"><c r="A1" t="inlineStr"><is><t>ph</t></is></c></row></sheetData></worksheet>',
        ]);
        $noSheet = $this->xlsxFile(['docProps/app.xml' => '<Properties/>']);
        $badXml = $this->xlsxFile(['xl/worksheets/sheet1.xml' => 'ceci n est pas du XML']);

        $provider = new CsvImportProvider();

        $this->assertSame([], $provider->setFilePath($headerOnly)->fetchObservations());
        $this->assertSame([], $provider->setFilePath($noSheet)->fetchObservations());
        $this->assertSame([], $provider->setFilePath($badXml)->fetchObservations());
    }

    public function test_file_with_xlsx_extension_that_is_not_a_zip_is_read_as_csv(): void
    {
        $observations = $this->parse("code;ph\nPT-FAUX-XLSX;7.7\n", 'xlsx');

        $this->assertCount(1, $observations);
        $this->assertSame('PT-FAUX-XLSX', $observations[0]->stationCode);
        $this->assertSame(7.7, $observations[0]->ph);
    }

    // -------------------------------------------------------------------------
    // Rattachement au point de mesure
    // -------------------------------------------------------------------------

    public function test_existing_point_is_found_by_id_code_numeric_code_or_name(): void
    {
        $byId = PointMesure::create(['code' => 'P-ID', 'nom' => 'Par identifiant', 'statut' => 'actif']);
        $byCode = PointMesure::create(['code' => 'P-CODE', 'nom' => 'Par code', 'statut' => 'actif']);
        $byName = PointMesure::create(['code' => 'P-NOM', 'nom' => 'Par nom', 'statut' => 'actif']);

        $provider = new CsvImportProvider();

        $this->assertTrue($byId->is($provider->resolveOrCreatePoint(
            $this->observation(['stationCode' => 'INCONNU'], ['point_mesure_id' => $byId->id])
        )));
        $this->assertTrue($byCode->is($provider->resolveOrCreatePoint(
            $this->observation(['stationCode' => 'P-CODE'])
        )));
        $this->assertTrue($byId->is($provider->resolveOrCreatePoint(
            $this->observation(['stationCode' => (string) $byId->id, 'stationName' => 'Autre'])
        )));
        $this->assertTrue($byName->is($provider->resolveOrCreatePoint(
            $this->observation(['stationCode' => 'INCONNU-2', 'stationName' => 'Par nom'])
        )));

        $this->assertSame(3, PointMesure::count(), 'Aucun point ne doit être créé');
    }

    public function test_new_point_is_attached_to_the_infrastructure_and_its_zone(): void
    {
        $zone = Zone::factory()->create(['nom' => 'Zone Infra']);
        $infra = Infrastructure::factory()->create(['zone_id' => $zone->id, 'reference' => 'INF-REF-01']);

        $provider = new CsvImportProvider();

        $byId = $provider->resolveOrCreatePoint($this->observation(
            ['stationCode' => 'PT-NEW-1', 'stationName' => 'Nouveau 1', 'latitude' => 36.5, 'longitude' => 10.2],
            ['infrastructure_id' => (string) $infra->id],
        ));
        $this->assertSame($infra->id, $byId->infrastructure_id);
        $this->assertSame($zone->id, $byId->zone_id);
        $this->assertSame('Zone Infra', $byId->zone);
        $this->assertEquals(36.5, (float) $byId->latitude);
        $this->assertSame('Station', $byId->type);
        $this->assertSame('actif', $byId->statut);

        $byReference = $provider->resolveOrCreatePoint($this->observation(
            ['stationCode' => 'PT-NEW-2', 'stationName' => 'Nouveau 2'],
            ['infrastructure_id' => '999999', 'infrastructure_reference' => 'INF-REF-01'],
        ));
        $this->assertSame($infra->id, $byReference->infrastructure_id);
        $this->assertNull($byReference->latitude, 'Une coordonnée 0 doit être enregistrée comme absente');

        // Le code de station lui-même peut être la référence de l'infrastructure.
        $byStationCode = $provider->resolveOrCreatePoint($this->observation(
            ['stationCode' => 'INF-REF-01', 'stationName' => 'Nouveau 3'],
        ));
        $this->assertSame($infra->id, $byStationCode->infrastructure_id);
    }

    public function test_new_point_zone_is_resolved_by_id_name_or_location(): void
    {
        $zoneById = Zone::factory()->create(['nom' => 'Zone A', 'localisation' => 'Tunis']);
        $zoneByLocation = Zone::factory()->create(['nom' => 'Zone B', 'localisation' => 'Sfax']);

        $provider = new CsvImportProvider();

        $point = $provider->resolveOrCreatePoint($this->observation(
            ['stationCode' => 'PT-Z1', 'stationName' => 'Z1'],
            ['zone_id' => (string) $zoneById->id],
        ));
        $this->assertSame($zoneById->id, $point->zone_id);
        $this->assertSame('Zone A', $point->zone);

        $point = $provider->resolveOrCreatePoint($this->observation(
            ['stationCode' => 'PT-Z2', 'stationName' => 'Z2'],
            ['zone_id' => '999999', 'zone' => 'Sfax'],
        ));
        $this->assertSame($zoneByLocation->id, $point->zone_id);

        // Zone inconnue : le nom fourni est conservé en texte, sans clé étrangère.
        $point = $provider->resolveOrCreatePoint($this->observation(
            ['stationCode' => 'PT-Z3', 'stationName' => 'Z3'],
            ['zone' => 'Zone Inconnue'],
        ));
        $this->assertNull($point->zone_id);
        $this->assertSame('Zone Inconnue', $point->zone);

        // Sans aucune information : zone par défaut.
        $point = $provider->resolveOrCreatePoint($this->observation(['stationCode' => 'PT-Z4', 'stationName' => 'Z4']));
        $this->assertSame('Zone Principale', $point->zone);
    }

    public function test_new_point_keeps_the_requested_identifier_and_generates_missing_labels(): void
    {
        $provider = new CsvImportProvider();

        $point = $provider->resolveOrCreatePoint($this->observation(
            ['stationCode' => '', 'stationName' => ''],
            ['point_mesure_id' => 4321],
        ));

        $this->assertSame(4321, $point->id);
        $this->assertSame('PT-4321', $point->code);
        $this->assertSame('PT-4321', $point->nom);
        $this->assertDatabaseHas('point_mesures', ['id' => 4321, 'code' => 'PT-4321']);
    }

    // -------------------------------------------------------------------------
    // Intégration avec le service de synchronisation
    // -------------------------------------------------------------------------

    public function test_reimporting_a_file_with_new_columns_backfills_missing_values(): void
    {
        $sync = app(ExternalSyncService::class);

        $first = $this->tempFile("code;date_mesure;ph\nPT-BF;2026-01-05 10:00:00;7.1\n");
        $result = $sync->runProvider((new CsvImportProvider())->setFilePath($first, 'labo'));
        $this->assertSame(1, $result['imported']);

        $mesure = MesureQualite::where('provider', 'csv')->firstOrFail();
        $this->assertNull($mesure->nitrates);

        // Même ligne (même identifiant externe) avec une colonne nitrates ajoutée.
        $second = $this->tempFile("code;date_mesure;ph;nitrates\nPT-BF;2026-01-05 10:00:00;9.9;12\n");
        $result = $sync->runProvider((new CsvImportProvider())->setFilePath($second, 'labo'));

        $this->assertSame(1, $result['imported']);
        $this->assertSame(1, MesureQualite::count(), 'Aucun doublon ne doit être créé');
        $mesure->refresh();
        $this->assertEquals(12.0, (float) $mesure->nitrates);
        $this->assertEquals(7.1, (float) $mesure->ph, 'Une valeur déjà stockée ne doit pas être écrasée');

        // Troisième passage identique : rien à compléter, la ligne est ignorée.
        $result = $sync->runProvider((new CsvImportProvider())->setFilePath($second, 'labo'));
        $this->assertSame(0, $result['imported']);
        $this->assertSame(1, $result['skipped']);
    }
}
