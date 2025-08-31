<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use League\Csv\Reader;
use App\Models\Airport;
use Illuminate\Support\Str;

class AirportSeeder extends Seeder
{
    public function run(): void
    {
        // Minimal city mapping
        $metroMap = [
            // Montreal
            'YUL' => 'YMQ', 'YHU' => 'YMQ', 'YMX' => 'YMQ',
            // Toronto
            'YYZ' => 'YTO', 'YTZ' => 'YTO', 'YHM' => 'YTO', 'YKF' => 'YTO',
            // New York
            'JFK' => 'NYC', 'LGA' => 'NYC', 'EWR' => 'NYC',
            // London
            'LHR' => 'LON', 'LGW' => 'LON', 'LCY' => 'LON', 'LTN' => 'LON', 'SEN' => 'LON', 'STN' => 'LON',
            // Paris
            'CDG' => 'PAR', 'ORY' => 'PAR', 'BVA' => 'PAR',
            // Tokyo
            'HND' => 'TYO', 'NRT' => 'TYO',
            // Washington
            'DCA' => 'WAS', 'IAD' => 'WAS', 'BWI' => 'WAS',
        ];

        $csv = Reader::createFromPath(database_path('seeders/data/airports.csv'), 'r');
        $csv->setHeaderOffset(0);

        foreach ($csv->getRecords() as $row) {
            $iata = strtoupper(trim($row['iata_code'] ?? ''));
            if ($iata === '') continue; // skip entries with no IATA

            $city = trim($row['municipality'] ?? '') ?: null;
            $timezone = trim($row['tz_database_time_zone'] ?? '') ?: null;

            // Derive cityCode
            $cityCode = $metroMap[$iata] ?? $iata;

            Airport::updateOrCreate(
                ['iata_code' => $iata],
                [
                    'id'       => (string) Str::uuid(),
                    'name'     => $row['name'] ?? '',
                    'city'     => $city,
                    'lat'      => is_numeric($row['latitude_deg'] ?? null) ? (float) $row['latitude_deg'] : null,
                    'lon'      => is_numeric($row['longitude_deg'] ?? null) ? (float) $row['longitude_deg'] : null,
                    'timezone' => $timezone,
                    'city_code'=> $cityCode,
                ]
            );
        }
    }
}
