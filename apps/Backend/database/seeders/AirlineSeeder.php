<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use League\Csv\Reader;
use App\Models\Airline;
use Illuminate\Support\Str;

class AirlineSeeder extends Seeder
{
    public function run(): void
    {
        $csv = Reader::createFromPath(database_path('seeders/data/airlines.csv'), 'r');
        $csv->setHeaderOffset(null); // no header in this file

        foreach ($csv->getRecords() as $row) {
            [$airlineId, $name, $alias, $iata] = $row;

            if ($iata === "\\N" || $iata === "-" || empty($iata)) {
                continue; // skip airlines without valid IATA code
            }

            Airline::updateOrCreate(
                ['iata_code' => $iata],
                [
                    'id' => (string) Str::uuid(),
                    'name' => $name,
                ]
            );
        }
    }
}
