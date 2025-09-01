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

            // Generate flights in pairs to ensure A→B and B→A routes exist
            $generatedCount = 0;

            while ($generatedCount < $NUM_FLIGHTS) {
                $air = $airlineRows[random_int(0, $nAirlines - 1)];
                
                // Pick two distinct airports by index
                $aIdx = random_int(0, $nAirports - 1);
                do {
                    $bIdx = random_int(0, $nAirports - 1);
                } while ($bIdx === $aIdx);

                $from = $airports[$aIdx];  // e.g., "YUL"
                $to   = $airports[$bIdx];  // e.g., "LAX"

                // Generate outbound flight (A→B)
                $outboundFlightNumber = $air['iata_code'].random_int(100, 999);
                $outboundDaysAhead = random_int(1, 365);
                $outboundDepDate = date('Y-m-d', $todayTs + 86400 * $outboundDaysAhead);
                $outboundDepMins = $randDep();
                $outboundDur = random_int(60, 720);
                $outboundArrMins = ($outboundDepMins + $outboundDur) % 1440;
                $outboundPrice = round(50 + $outboundDur * 0.6 + random_int(0, 50), 2);

                $rows[] = [
                    'id'                => (string) Str::uuid(),
                    'flight_number'     => $outboundFlightNumber,
                    'airline_id'        => $air['id'],
                    'departure_airport' => $from,
                    'arrival_airport'   => $to,
                    'departure_date'    => $outboundDepDate,
                    'departure_time'    => $fmt($outboundDepMins),
                    'arrival_time'      => $fmt($outboundArrMins),
                    'price'             => $outboundPrice,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
                $generatedCount++;

                // Generate return flight (B→A) if we haven't reached the limit
                if ($generatedCount < $NUM_FLIGHTS) {
                    $returnFlightNumber = $air['iata_code'].random_int(100, 999);
                    $returnDaysAhead = random_int(1, 365);
                    $returnDepDate = date('Y-m-d', $todayTs + 86400 * $returnDaysAhead);
                    $returnDepMins = $randDep();
                    $returnDur = random_int(60, 720);
                    $returnArrMins = ($returnDepMins + $returnDur) % 1440;
                    $returnPrice = round(50 + $returnDur * 0.6 + random_int(0, 50), 2);

                    $rows[] = [
                        'id'                => (string) Str::uuid(),
                        'flight_number'     => $returnFlightNumber,
                        'airline_id'        => $air['id'],
                        'departure_airport' => $to,      // B→A
                        'arrival_airport'   => $from,    // B→A
                        'departure_date'    => $returnDepDate,
                        'departure_time'    => $fmt($returnDepMins),
                        'arrival_time'      => $fmt($returnArrMins),
                        'price'             => $returnPrice,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];
                    $generatedCount++;
                }

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
