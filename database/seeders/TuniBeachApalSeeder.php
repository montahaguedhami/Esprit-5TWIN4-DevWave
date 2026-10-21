<?php

namespace Database\Seeders;

use App\Models\Beach;
use App\Models\Governorate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class TuniBeachApalSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/tunibeach/apal_beaches.csv');
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException('Could not open the APAL beach catalogue CSV.');
        }

        $prefix = fread($handle, 3);
        if ($prefix !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle, null, ',', '"', '');
        if (! is_array($header)) {
            fclose($handle);
            throw new RuntimeException('The APAL beach catalogue CSV has no header row.');
        }

        if ($header !== ['source_row', 'governorate_name', 'beach_name']) {
            fclose($handle);
            throw new RuntimeException('The APAL beach catalogue CSV columns do not match the expected format.');
        }

        $createdGovernorates = 0;
        $createdBeaches = 0;
        $seenSlugs = [];

        try {
            DB::transaction(function () use ($handle, &$createdGovernorates, &$createdBeaches, &$seenSlugs): void {
                while (($row = fgetcsv($handle, null, ',', '"', '')) !== false) {
                    [$sourceRow, $governorateName, $beachName] = array_pad($row, 3, '');
                    $governorateName = trim((string) $governorateName);
                    $beachName = trim((string) $beachName);

                    if ($governorateName === '' || $beachName === '') {
                        continue;
                    }

                    $governorateSlug = Str::slug($governorateName);
                    $governorate = Governorate::firstOrCreate(
                        ['slug' => $governorateSlug],
                        ['name' => $governorateName, 'is_verified' => true, 'is_published' => true],
                    );
                    $createdGovernorates += (int) $governorate->wasRecentlyCreated;

                    $slug = Str::slug($governorateSlug.'-'.$beachName);
                    if ($slug === '') {
                        $slug = 'apal-beach-'.(int) $sourceRow;
                    }
                    if (isset($seenSlugs[$slug])) {
                        $slug .= '-'.(int) $sourceRow;
                    }
                    $seenSlugs[$slug] = true;

                    $beach = Beach::firstOrCreate(
                        ['slug' => $slug],
                        [
                            'governorate_id' => $governorate->id,
                            'name' => $beachName,
                            'description' => null,
                            'latitude' => null,
                            'longitude' => null,
                            'coordinates_verified' => false,
                            'is_published' => true,
                        ],
                    );
                    $createdBeaches += (int) $beach->wasRecentlyCreated;
                }
            });
        } finally {
            fclose($handle);
        }

        $this->command?->info("APAL catalogue import complete: {$createdGovernorates} governorates and {$createdBeaches} beaches added.");
        $this->command?->warn('No coordinates, water measurements, or bathing decisions were imported. Bathing status remains unknown.');
    }
}
