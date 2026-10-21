<?php

namespace Database\Seeders;

use App\Models\BeachHistoricalAdvisoryPoint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TuniBeachHistoricalAdvisorySeeder extends Seeder
{
    private const REPORT_DATE = '2026-06-20';
    private const SOURCE_NAME = 'Relevé historique détaillé fourni — total annoncé par TAP';
    private const SOURCE_URL = 'https://www.tap.info.tn/fr/Portail-Titres-de-l-actualit%C3%A9/20310648-interdiction-de';

    public function run(): void
    {
        $path = database_path('data/tunibeach/historical_advisory_points.csv');
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new RuntimeException('Could not open the historical TuniBeach advisory CSV.');
        }

        try {
            $header = fgetcsv($handle, null, ',', '"', '');
            if ($header !== ['governorate_name', 'point_name', 'reported_status']) {
                throw new RuntimeException('The historical TuniBeach advisory CSV has an unexpected header.');
            }

            DB::transaction(function () use ($handle): void {
                DB::table('tunibeach_historical_advisory_points')
                    ->whereDate('reported_at', self::REPORT_DATE)
                    ->delete();

                while (($row = fgetcsv($handle, null, ',', '"', '')) !== false) {
                    [$governorate, $point, $reportedStatus] = array_pad($row, 3, '');
                    $governorate = trim((string) $governorate);
                    $point = trim((string) $point);
                    $reportedStatus = trim((string) $reportedStatus);
                    if ($governorate === '' || $point === '' || ! in_array($reportedStatus, ['restricted', 'unknown'], true)) {
                        throw new RuntimeException('A historical advisory CSV row is incomplete or has an invalid reported status.');
                    }

                    BeachHistoricalAdvisoryPoint::create([
                        'reported_at' => self::REPORT_DATE,
                        'governorate_name' => $governorate,
                        'point_name' => $point,
                        'reported_status' => $reportedStatus,
                        'source_name' => self::SOURCE_NAME,
                        'source_url' => self::SOURCE_URL,
                    ]);
                }
            });
        } finally {
            fclose($handle);
        }

        $this->command?->info('Replaced the June 20, 2026 historical advisory list from the CSV. These records do not determine current beach status.');
    }
}
