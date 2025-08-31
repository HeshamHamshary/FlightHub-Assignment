<?php

namespace Database\Seeders;

use App\Models\Airport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use League\Csv\Reader;

class AirportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvPath = database_path('seeders/data/airports.csv');
        $csv = Reader::createFromPath($csvPath, 'r');
        $csv->setHeaderOffset(0);

        foreach ($csv->getRecords() as $row) {
            $iata = trim($row['iata_code'] ?? '');
            if ($iata === '') continue; // skip entries without IATA

            Airport::updateOrCreate(
                ['iata_code' => $iata],
                [
                    'id'       => (string) Str::uuid(),
                    'name'     => $row['name'] ?? '',
                    'city'     => $row['municipality'] ?? null,
                    'country'  => $row['iso_country'] ?? null,
                    'lat'      => is_numeric($row['latitude_deg'] ?? null) ? $row['latitude_deg'] : null,
                    'lon'      => is_numeric($row['longitude_deg'] ?? null) ? $row['longitude_deg'] : null,
                    'timezone' => $row['tz_database_time_zone'] ?? null,
                ]
            );
        }
    }
}
