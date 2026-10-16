<?php

namespace App\Services\ExternalData;

use App\Models\PointMesure;
use Illuminate\Support\Facades\Log;

/**
 * Manual CSV/XLSX import provider.
 *
 * Usage:
 *   $provider = app(CsvImportProvider::class);
 *   $provider->setFilePath('/path/to/anpe_export.csv');
 *   $observations = $provider->fetchObservations();
 *
 * Expected CSV columns (case-insensitive, order flexible):
 *   code, nom, zone, latitude, longitude, date_mesure,
 *   ph, turbidite, chlore_residuel, plomb, nitrates
 *
 * Optional: gouvernorat, region, type_station, source_name
 *
 * XLSX is supported if the phpoffice/phpspreadsheet package is available.
 * If it is not installed, the file is attempted as CSV.
 *
 * All imported rows are tagged source_type='real' because manual imports from
 * official agencies (ANPE, SONEDE, ONAS) are field observations.
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
            default        => $this->parseCsvFile($this->filePath),
        };
    }

    public function resolveOrCreatePoint(NormalisedObservation $obs): ?PointMesure
    {
        $point = PointMesure::where('code', $obs->stationCode)->first();
        if ($point) {
            return $point;
        }

        $latitude = ($obs->latitude >= -90 && $obs->latitude <= 90) ? $obs->latitude : null;
        $longitude = ($obs->longitude >= -180 && $obs->longitude <= 180) ? $obs->longitude : null;

        if ($obs->latitude == 0.0 && $obs->longitude == 0.0) {
            $latitude = null;
            $longitude = null;
        }

        return PointMesure::create([
            'code'        => $obs->stationCode,
            'nom'         => $obs->stationName,
            'zone'        => $obs->rawMeta['zone'] ?? $obs->rawMeta['gouvernorat'] ?? null,
            'latitude'    => $latitude,
            'longitude'   => $longitude,
            'type'        => $obs->rawMeta['type_station'] ?? 'Station',
            'statut'      => 'actif',
            'description' => 'Cree par import ' . ($obs->rawMeta['source_name'] ?? 'manuel'),
        ]);
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

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if ($row === [null] || $row === false) {
                continue;
            }

            if ($headers === null) {
                $headers = array_map(fn ($h) => strtolower(trim((string) $h)), $row);
                // Remove a UTF-8 BOM from the first header (common in Excel exports).
                if ($headers !== [] && str_starts_with($headers[0], "\xef\xbb\xbf")) {
                    $headers[0] = trim(substr($headers[0], 3));
                }
                while ($headers !== [] && end($headers) === '') {
                    array_pop($headers);
                }
                continue;
            }

            // Pad short rows so array_combine never fails on trailing empty columns.
            if (count($row) < count($headers)) {
                $row = array_pad($row, count($headers), '');
            }

            $data    = array_combine($headers, array_slice($row, 0, count($headers)));
            $parsed  = $this->parseDataRow($data);
            if ($parsed !== null) {
                $observations[] = $parsed;
            }
        }

        fclose($handle);
        return $observations;
    }

    /**
     * Detect comma, semicolon or tab separators automatically.
     */
    private function detectDelimiter($handle): string
    {
        $firstLine = '';
        while (($line = fgets($handle)) !== false) {
            $trimmed = trim($line);
            if ($trimmed !== '') {
                $firstLine = $trimmed;
                break;
            }
        }

        $counts = [
            ',' => substr_count($firstLine, ','),
            ';' => substr_count($firstLine, ';'),
            "\t" => substr_count($firstLine, "\t"),
        ];

        arsort($counts);
        $delimiter = (string) array_key_first($counts);

        return $counts[$delimiter] > 0 ? $delimiter : ',';
    }

    private function parseXlsx(string $path): array
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            Log::warning('CsvImportProvider: phpoffice/phpspreadsheet not installed; attempting CSV parse');
            return $this->parseCsvFile($path);
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray();

            if (count($rows) < 2) {
                return [];
            }

            $headers = array_map(fn ($h) => strtolower(trim((string) $h)), array_shift($rows));
            $observations = [];

            foreach ($rows as $row) {
                $data   = array_combine($headers, array_map('strval', $row));
                $parsed = $this->parseDataRow($data);
                if ($parsed !== null) {
                    $observations[] = $parsed;
                }
            }

            return $observations;
        } catch (\Throwable $e) {
            Log::error('CsvImportProvider: XLSX parse error', ['message' => $e->getMessage()]);
            return [];
        }
    }

    private function parseDataRow(array $row): ?NormalisedObservation
    {
        $code = trim($row['code'] ?? $row['code_station'] ?? '');
        $name = trim($row['nom'] ?? $row['nom_station'] ?? $row['name'] ?? $code);

        if ($code === '') {
            return null;
        }

        $lat = isset($row['latitude'])  && $row['latitude']  !== '' ? (float) $row['latitude']  : null;
        $lon = isset($row['longitude']) && $row['longitude'] !== '' ? (float) $row['longitude'] : null;

        $dateRaw = $row['date_mesure'] ?? $row['date'] ?? null;
        $date    = null;
        if ($dateRaw && trim($dateRaw) !== '') {
            try {
                $date = new \DateTimeImmutable(trim($dateRaw));
            } catch (\Throwable) {
                $date = null;
            }
        }
        $date ??= new \DateTimeImmutable();

        $ph    = $this->floatOrNull($row['ph'] ?? null);
        $turb  = $this->floatOrNull($row['turbidite'] ?? $row['turbidity'] ?? null);
        $no3   = $this->floatOrNull($row['nitrates'] ?? null);
        $clore = $this->floatOrNull($row['chlore_residuel'] ?? $row['chlorine'] ?? null);
        $plomb = $this->floatOrNull($row['plomb'] ?? $row['lead'] ?? null);

        if ($ph === null && $turb === null && $no3 === null) {
            return null; // No measurable parameters — skip
        }

        $externalId = 'csv-' . md5("{$code}-{$dateRaw}-{$this->sourceName}");

        return new NormalisedObservation(
            externalId:      $externalId,
            provider:        'csv',
            stationCode:     $code,
            stationName:     $name ?: $code,
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
                'zone'        => $row['zone'] ?? $row['gouvernorat'] ?? null,
                'type_station'=> $row['type'] ?? $row['type_station'] ?? null,
                'source_name' => $this->sourceName,
            ],
        );
    }

    private function floatOrNull(mixed $val): ?float
    {
        if ($val === null || trim((string) $val) === '') {
            return null;
        }
        $f = filter_var(str_replace(',', '.', $val), FILTER_VALIDATE_FLOAT);
        return $f === false ? null : $f;
    }
}
