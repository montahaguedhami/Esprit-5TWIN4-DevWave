<?php

namespace App\Services\ExternalData;

use App\Models\PointMesure;
use App\Models\Infrastructure;
use App\Models\Zone;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Manual CSV/XLSX import provider.
 */
class CsvImportProvider implements ExternalDataProviderInterface
{
    private ?string $filePath = null;
    private string $sourceName = 'import_manuel';

    public function setFilePath(string $path, string $sourceName = 'import_manuel'): self
    {
        $this->filePath   = $path;
        $this->sourceName = $sourceName;
        return $this;
    }

    public function getProviderSlug(): string
    {
        return 'csv';
    }

    public function getProviderName(): string
    {
        return 'Import manuel CSV/XLSX';
    }

    public function fetchObservations(array $options = []): array
    {
        if ($this->filePath === null) {
            return [];
        }

        if (!file_exists($this->filePath)) {
            Log::warning('CsvImportProvider: file not found', ['path' => $this->filePath]);
            return [];
        }

        $ext = strtolower(pathinfo($this->filePath, PATHINFO_EXTENSION));

        return match ($ext) {
            'xlsx', 'xls' => $this->parseXlsx($this->filePath),
            default       => $this->parseCsvFile($this->filePath),
        };
    }

    public function resolveOrCreatePoint(NormalisedObservation $obs): ?PointMesure
    {
        $pointId = $obs->rawMeta['point_mesure_id'] ?? null;
        $point = null;

        // 1. Chercher par ID de point si fourni
        if ($pointId && is_numeric($pointId)) {
            $point = PointMesure::find((int) $pointId);
        }

        // 2. Chercher par code exact
        if (!$point && !empty($obs->stationCode)) {
            $point = PointMesure::where('code', $obs->stationCode)->first();
        }

        // 3. Chercher par identifiant numérique si le code est un nombre
        if (!$point && is_numeric($obs->stationCode)) {
            $point = PointMesure::find((int) $obs->stationCode);
        }

        // 4. Chercher par nom exact
        if (!$point && !empty($obs->stationName)) {
            $point = PointMesure::where('nom', $obs->stationName)->first();
        }

        if ($point) {
            return $point;
        }

        $infrastructure = $this->resolveInfrastructure($obs);
        $zone = $infrastructure?->zone ?? $this->resolveZone($obs);

        $code = !empty($obs->stationCode) ? $obs->stationCode : ('PT-' . ($pointId ?? rand(100, 999)));
        $name = !empty($obs->stationName) ? $obs->stationName : $code;

        $newPoint = new PointMesure([
            'code'        => $code,
            'nom'         => $name,
            'zone'        => $zone?->nom ?? $infrastructure?->zone?->nom ?? $obs->rawMeta['zone'] ?? 'Zone Principale',
            'zone_id'     => $infrastructure?->zone_id ?? $zone?->id,
            'infrastructure_id' => $infrastructure?->id,
            'latitude'    => ($obs->latitude != 0.0) ? $obs->latitude : null,
            'longitude'   => ($obs->longitude != 0.0) ? $obs->longitude : null,
            'type'        => 'Station',
            'statut'      => 'actif',
            'description' => 'Station de prélèvement d’eau',
        ]);

        if ($pointId && is_numeric($pointId)) {
            $newPoint->id = (int) $pointId;
        }
        $newPoint->save();

        return $newPoint;
    }

    // ──────────────────────────────────────────────────────────────────────────

    private function parseCsvFile(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            Log::error('CsvImportProvider: cannot open file', ['path' => $path]);
            return [];
        }

        $delimiter = $this->detectDelimiter($handle);
        rewind($handle);

        $headers = null;
        $observations = [];
        $rowIdx = 0;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowIdx++;
            if ($row === [null] || $row === false || empty(array_filter($row, fn ($c) => trim((string)$c) !== ''))) {
                continue;
            }

            if ($headers === null) {
                $headers = array_map(fn ($h) => $this->normaliseHeader($h), $row);
                while ($headers !== [] && end($headers) === '') {
                    array_pop($headers);
                }
                continue;
            }

            if (count($row) < count($headers)) {
                $row = array_pad($row, count($headers), '');
            }

            $data = array_combine($headers, array_slice($row, 0, count($headers)));
            $parsed = $this->parseDataRow($data, $rowIdx);
            if ($parsed !== null) {
                $observations[] = $parsed;
            }
        }

        fclose($handle);
        return $observations;
    }

    private function detectDelimiter($handle): string
    {
        $firstLine = '';
        while (($line = fgets($handle)) !== false) {
            $trimmed = trim(preg_replace('/^\xEF\xBB\xBF/', '', $line));
            if ($trimmed !== '') {
                $firstLine = $trimmed;
                break;
            }
        }

        $counts = [
            ';'  => substr_count($firstLine, ';'),
            ','  => substr_count($firstLine, ','),
            "\t" => substr_count($firstLine, "\t"),
            '|'  => substr_count($firstLine, '|'),
        ];

        arsort($counts);
        $delimiter = (string) array_key_first($counts);

        return $counts[$delimiter] > 0 ? $delimiter : ',';
    }

    private function parseXlsx(string $path): array
    {
        if (class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
                $sheet       = $spreadsheet->getActiveSheet();
                $rows        = $sheet->toArray();

                if (count($rows) >= 2) {
                    $headers = array_map(fn ($h) => $this->normaliseHeader($h), array_shift($rows));
                    $observations = [];
                    $idx = 0;
                    foreach ($rows as $row) {
                        $idx++;
                        if (empty(array_filter($row, fn ($c) => trim((string)$c) !== ''))) continue;
                        if (count($row) < count($headers)) {
                            $row = array_pad($row, count($headers), '');
                        }
                        $data = array_combine($headers, array_slice(array_map('strval', $row), 0, count($headers)));
                        $parsed = $this->parseDataRow($data, $idx);
                        if ($parsed !== null) {
                            $observations[] = $parsed;
                        }
                    }
                    return $observations;
                }
            } catch (\Throwable $e) {
                Log::warning('PhpSpreadsheet parse error: ' . $e->getMessage());
            }
        }

        if (!class_exists(\ZipArchive::class)) {
            return $this->parseCsvFile($path);
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return $this->parseCsvFile($path);
        }

        $sharedStrings = [];
        $stringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($stringsXml) {
            $sxml = @simplexml_load_string($stringsXml);
            if ($sxml && isset($sxml->si)) {
                foreach ($sxml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                    } elseif (isset($si->r)) {
                        $text = '';
                        foreach ($si->r as $r) {
                            $text .= (string) $r->t;
                        }
                        $sharedStrings[] = $text;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (!$sheetXml) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (str_starts_with($name, 'xl/worksheets/sheet') && str_ends_with($name, '.xml')) {
                    $sheetXml = $zip->getFromIndex($i);
                    break;
                }
            }
        }
        $zip->close();

        if (!$sheetXml) {
            return [];
        }

        $xml = @simplexml_load_string($sheetXml);
        if (!$xml || !isset($xml->sheetData->row)) {
            return [];
        }

        $rows = [];
        foreach ($xml->sheetData->row as $r) {
            $rowValues = [];
            foreach ($r->c as $c) {
                $ref = (string) $c['r'];
                preg_match('/([A-Z]+)/', $ref, $m);
                $colLetters = $m[1] ?? 'A';
                $colIndex = 0;
                for ($len = strlen($colLetters), $k = 0; $k < $len; $k++) {
                    $colIndex = $colIndex * 26 + (ord($colLetters[$k]) - 64);
                }
                $colIndex -= 1;

                $type = (string) $c['t'];
                $val = (string) $c->v;
                if ($type === 's' && isset($sharedStrings[(int) $val])) {
                    $val = $sharedStrings[(int) $val];
                } elseif ($type === 'inlineStr' && isset($c->is->t)) {
                    $val = (string) $c->is->t;
                }
                $rowValues[$colIndex] = $val;
            }

            if ($rowValues !== []) {
                $maxKey = max(array_keys($rowValues));
                $filled = [];
                for ($idx = 0; $idx <= $maxKey; $idx++) {
                    $filled[$idx] = $rowValues[$idx] ?? '';
                }
                $rows[] = $filled;
            }
        }

        if (count($rows) < 2) {
            return [];
        }

        $headers = array_map(fn ($h) => $this->normaliseHeader($h), array_shift($rows));
        $observations = [];
        $idx = 0;
        foreach ($rows as $row) {
            $idx++;
            if (empty(array_filter($row, fn ($c) => trim((string)$c) !== ''))) continue;
            if (count($row) < count($headers)) {
                $row = array_pad($row, count($headers), '');
            }
            $data = array_combine($headers, array_slice($row, 0, count($headers)));
            $parsed = $this->parseDataRow($data, $idx);
            if ($parsed !== null) {
                $observations[] = $parsed;
            }
        }

        return $observations;
    }

    private function parseDataRow(array $row, int $rowIdx = 0): ?NormalisedObservation
    {
        $pointMesureId = $this->firstValue($row, ['point_mesure_id', 'id_point_mesure', 'point_id', 'id_point']);
        $rawReference = trim((string) $this->firstValue($row, ['reference', 'ref', 'code_mesure', 'ref_mesure']));
        $cleanReference = null;
        if ($rawReference !== '') {
            $cleanReference = preg_replace('/^DEMO[-_]/i', 'MES-', $rawReference);
        }

        $infrastructureReference = trim((string) $this->firstValue($row, [
            'infrastructure_reference', 'infrastructure_code', 'reference_infrastructure',
            'infrastructure', 'infra_ref', 'infra'
        ]));
        $infrastructureId = trim((string) $this->firstValue($row, [
            'infrastructure_id', 'id_infrastructure', 'infra_id'
        ]));

        $code = trim((string) $this->firstValue($row, [
            'code', 'code_station', 'station_code', 'code_point', 'point_code',
            'point', 'point_mesure', 'point_de_mesure', 'point_prelevement',
            'station', 'site', 'sampling_point'
        ]));

        if ($code === '' && $pointMesureId) {
            $code = 'PT-' . str_pad($pointMesureId, 3, '0', STR_PAD_LEFT);
        } elseif ($code === '' && $infrastructureReference !== '') {
            $code = $infrastructureReference;
        } elseif ($code === '' && $infrastructureId !== '') {
            $code = 'INF-' . $infrastructureId;
        }

        $derivedName = null;
        if ($rawReference !== '') {
            $stripped = preg_replace('/^(DEMO|MES|REF)[-_]/i', '', $rawReference);
            $stripped = preg_replace('/[-_][0-9]+$/', '', $stripped);
            if (!empty($stripped)) {
                $derivedName = ucwords(strtolower(str_replace(['-', '_'], ' ', $stripped)));
            }
        }

        $name = trim((string) $this->firstValue($row, [
            'nom', 'nom_station', 'station_name', 'nom_point', 'point_nom',
            'station', 'point', 'point_mesure', 'point_de_mesure',
            'libelle', 'designation', 'name'
        ]));

        if ($name === '' && $derivedName) {
            $name = 'Station ' . $derivedName;
        } elseif ($name === '' && $code !== '') {
            $name = $code;
        }

        if ($code === '') {
            $code = 'PT-IMP-' . ($rowIdx > 0 ? $rowIdx : rand(100, 999));
            $name = $name ?: $code;
        }

        $lat = $this->floatOrNull($this->firstValue($row, ['latitude', 'lat']));
        $lon = $this->floatOrNull($this->firstValue($row, ['longitude', 'lon', 'lng']));

        $dateRaw = $this->firstValue($row, [
            'date_mesure', 'date', 'date_prelevement', 'date_prel', 'date_analyse',
            'date_observation', 'date_heure', 'horodatage', 'datetime', 'timestamp',
            'date_collecte', 'created_at'
        ]);
        $date = $this->parseDateOrNull($dateRaw) ?? new \DateTimeImmutable();

        $ph    = $this->floatOrNull($this->firstValue($row, [
            'ph', 'ph_value', 'potential_hydrogen', 'valeur_ph', 'potentiel_hydrogene',
            'ph_eau', 'ph_mesure', 'unite_ph', 'ph_unites'
        ]));
        $turb  = $this->floatOrNull($this->firstValue($row, [
            'turbidite', 'turbidity', 'turbidity_ntu', 'turbidite_ntu', 'turb', 'turb_ntu',
            'turbidite_nfu', 'ntu', 'valeur_turbidite'
        ]));
        $no3   = $this->floatOrNull($this->firstValue($row, [
            'nitrates', 'nitrate', 'nitrates_mg_l', 'nitrate_mg_l', 'no3', 'no3_mg_l',
            'nitrates_no3', 'nitrate_no3', 'valeur_nitrates'
        ]));
        $clore = $this->floatOrNull($this->firstValue($row, [
            'chlore_residuel', 'chlore_residuel_mg_l', 'chlore_residuel_en_mg_l',
            'chlore', 'chlore_mg_l', 'chlorine', 'chlorine_mg_l', 'residual_chlorine',
            'residual_chlorine_mg_l', 'free_chlorine', 'cl2', 'chlore_libre', 'chlore_total',
            'valeur_chlore'
        ]));
        $plomb = $this->floatOrNull($this->firstValue($row, [
            'plomb', 'plomb_ug_l', 'plomb_ppb', 'plomb_pb_ug_l', 'plomb_pb_ppb',
            'lead', 'lead_ug_l', 'lead_ppb', 'lead_concentration', 'pb', 'pb_ug_l',
            'plomb_mg_l', 'valeur_plomb'
        ]));

        if ($ph === null && $turb === null && $no3 === null && $clore === null && $plomb === null) {
            return null;
        }

        $dateKey = $date->format('YmdHi');
        $externalId = 'csv-' . md5("{$code}-{$dateKey}-{$rowIdx}-{$this->sourceName}");

        return new NormalisedObservation(
            externalId:      $externalId,
            provider:        'csv',
            stationCode:     $code,
            stationName:     $name,
            latitude:        $lat ?? 0.0,
            longitude:       $lon ?? 0.0,
            observationDate: $date,
            ph:              $ph,
            turbidite:       $turb,
            nitrates:        $no3,
            chloreResiduel:  $clore,
            plomb:           $plomb,
            qualityFlag:     null,
            rawMeta: [
                'point_mesure_id' => $pointMesureId ? (int)$pointMesureId : null,
                'custom_reference' => $cleanReference,
                'zone'            => $row['zone'] ?? $row['zone_name'] ?? $row['zone_nom'] ?? $row['nom_zone'] ?? $row['gouvernorat'] ?? null,
                'zone_id'         => $row['zone_id'] ?? $row['id_zone'] ?? null,
                'infrastructure_id' => $infrastructureId ?: null,
                'infrastructure_reference' => $infrastructureReference ?: null,
                'type_station'    => $row['type'] ?? $row['type_station'] ?? null,
                'source_name'     => $this->sourceName,
                'source_donnee'   => 'manuel',
            ],
        );
    }

    private function normaliseHeader(mixed $header): string
    {
        $header = preg_replace('/^\xEF\xBB\xBF/', '', trim((string) $header));
        $header = Str::ascii(mb_strtolower($header));

        return trim((string) preg_replace('/[^a-z0-9]+/', '_', $header), '_');
    }

    private function firstValue(array $row, array $aliases): mixed
    {
        foreach ($aliases as $alias) {
            if (array_key_exists($alias, $row) && trim((string) $row[$alias]) !== '') {
                return $row[$alias];
            }
        }

        foreach ($row as $k => $v) {
            if (trim((string) $v) === '') continue;
            foreach ($aliases as $alias) {
                if (str_contains($k, $alias)) {
                    return $v;
                }
            }
        }

        return null;
    }

    private function resolveInfrastructure(NormalisedObservation $observation): ?Infrastructure
    {
        $id = filter_var($observation->rawMeta['infrastructure_id'] ?? null, FILTER_VALIDATE_INT);
        if ($id !== false && $id !== null) {
            $infrastructure = Infrastructure::with('zone')->find($id);
            if ($infrastructure) {
                return $infrastructure;
            }
        }

        $reference = trim((string) (
            $observation->rawMeta['infrastructure_reference']
            ?? $observation->rawMeta['infrastructure_code']
            ?? ''
        ));
        if ($reference !== '') {
            $infrastructure = Infrastructure::with('zone')->where('reference', $reference)->first();
            if ($infrastructure) {
                return $infrastructure;
            }
        }

        return Infrastructure::with('zone')->where('reference', $observation->stationCode)->first();
    }

    private function resolveZone(NormalisedObservation $observation): ?Zone
    {
        $id = filter_var($observation->rawMeta['zone_id'] ?? null, FILTER_VALIDATE_INT);
        if ($id !== false && $id !== null) {
            $zone = Zone::find($id);
            if ($zone) {
                return $zone;
            }
        }

        $name = trim((string) ($observation->rawMeta['zone'] ?? $observation->rawMeta['gouvernorat'] ?? ''));
        if ($name === '') {
            return null;
        }

        return Zone::query()
            ->where('nom', $name)
            ->orWhere('localisation', $name)
            ->first();
    }

    private function floatOrNull(mixed $val): ?float
    {
        if ($val === null) {
            return null;
        }

        $str = trim((string) $val);
        $str = str_replace(["\xc2\xa0", "\u{00A0}", ' '], '', $str);

        if ($str === '' || in_array(strtolower($str), ['nd', 'na', 'n/a', 'null', 'none', '-'], true)) {
            return null;
        }

        $str = str_replace(',', '.', $str);

        if (preg_match('/[-+]?[0-9]*\.?[0-9]+/', $str, $matches)) {
            return (float) $matches[0];
        }

        return null;
    }

    private function parseDateOrNull(mixed $val): ?\DateTimeImmutable
    {
        if ($val === null || trim((string) $val) === '') {
            return null;
        }

        $raw = trim((string) $val);

        if (is_numeric($raw) && (float) $raw > 30000 && (float) $raw < 70000) {
            $seconds = ((float) $raw - 25569) * 86400;
            return (new \DateTimeImmutable())->setTimestamp((int) round($seconds));
        }

        if (str_contains($raw, '/')) {
            $formats = [
                'd/m/Y H:i:s', 'd/m/Y H:i', 'd/m/Y',
                'd/m/y H:i:s', 'd/m/y H:i', 'd/m/y',
                'm/d/Y H:i:s', 'm/d/Y H:i', 'm/d/Y',
            ];
            foreach ($formats as $fmt) {
                $d = \DateTimeImmutable::createFromFormat($fmt, $raw);
                if ($d !== false) {
                    return $d;
                }
            }
        }

        if (str_contains($raw, '-')) {
            $formats = [
                'Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d',
                'd-m-Y H:i:s', 'd-m-Y H:i', 'd-m-Y',
            ];
            foreach ($formats as $fmt) {
                $d = \DateTimeImmutable::createFromFormat($fmt, $raw);
                if ($d !== false) {
                    return $d;
                }
            }
        }

        try {
            return new \DateTimeImmutable($raw);
        } catch (\Throwable) {
            return null;
        }
    }
}
