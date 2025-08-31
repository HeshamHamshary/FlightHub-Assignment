<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Airport;
use App\Models\Airline;

class FlightSeeder extends Seeder
{
    public function run(): void
    {
        $NUM_FLIGHTS = 10000;     // how many flights to generate
        $BATCH_SIZE  = 1000;     // insert batch size

        // 1) Load only what we need, once
        $airports = Airport::whereNotNull('iata_code')
            ->pluck('iata_code')  
            ->values()
            ->all();

        $airlineRows = Airline::whereNotNull('iata_code')
            ->get(['id','iata_code']) 
            ->toArray();

        if (count($airports) < 2 || count($airlineRows) === 0) {
            $this->command->warn("Need at least 2 airports and 1 airline to seed flights.");
            return;
        }

        DB::disableQueryLog();

        $nAirports = count($airports);
        $nAirlines = count($airlineRows);
        $todayTs   = time();

        // small helper to format minutes to "HH:MM"
        $fmt = static function (int $mins): string {
            $mins = ($mins % 1440 + 1440) % 1440;
            $h = intdiv($mins, 60);
            $m = $mins % 60;
            return sprintf('%02d:%02d', $h, $m);
        };

        // random minutes block
        $randDep = static function (): int {
            $start = 5 * 60;  // 05:00
            $end   = 22 * 60 + 55; // 22:55
            $step  = 5;
            $slots = intdiv(($end - $start), $step) + 1;
            return $start + (random_int(0, $slots - 1) * $step);
        };

        DB::transaction(function () use (
            $NUM_FLIGHTS, $BATCH_SIZE, $airports, $airlineRows, $nAirports, $nAirlines,
            $todayTs, $fmt, $randDep
        ) {
            $rows = [];

            for ($i = 0; $i < $NUM_FLIGHTS; $i++) {
                $air = $airlineRows[random_int(0, $nAirlines - 1)];
                $flightNumber = $air['iata_code'] . random_int(100, 999);

                // Pick two distinct airports by index
                $aIdx = random_int(0, $nAirports - 1);
                do {
                    $bIdx = random_int(0, $nAirports - 1);
                } while ($bIdx === $aIdx);

                $from = $airports[$aIdx];  // e.g., "YUL"
                $to   = $airports[$bIdx];  // e.g., "LAX"

                // Random date in next 365 days
                $daysAhead = random_int(1, 365);
                $depDate   = date('Y-m-d', $todayTs + 86400 * $daysAhead);

                // Local times and duration
                $depMins = $randDep(); 
                $dur     = random_int(60, 720);  
                $arrMins = ($depMins + $dur) % 1440;

                // Simple distance-based price model
                $price = round(50 + $dur * 0.6 + random_int(0, 50), 2);

                $rows[] = [
                    'id'                => (string) Str::uuid(),
                    'flight_number'     => $flightNumber,
                    'airline_id'        => $air['id'],
                    'departure_airport' => $from,
                    'arrival_airport'   => $to,
                    'departure_date'    => $depDate,
                    'departure_time'    => $fmt($depMins),
                    'arrival_time'      => $fmt($arrMins),
                    'price'             => $price,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];

                // Batch insert 
                if (count($rows) >= $BATCH_SIZE) {
                    DB::table('flights')->insert($rows);
                    $rows = [];
                }
            }

            if (!empty($rows)) {
                DB::table('flights')->insert($rows);
            }
        });
    }
}
