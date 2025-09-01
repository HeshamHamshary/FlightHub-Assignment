<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Trip;
use App\Models\Flight;

class TripSeeder extends Seeder
{
    public function run(): void
    {
        $NUM_TRIPS = 10000;     // how many trips to generate
        $BATCH_SIZE = 1000;     // insert batch size

        // Get all flight IDs for random selection
        $flightIds = Flight::pluck('id')->values()->all();
        $flightPrices = Flight::pluck('price', 'id')->all();

        if (count($flightIds) < 2) {
            $this->command->warn("Need at least 2 flights to seed trips. Please run FlightSeeder first.");
            return;
        }

        DB::disableQueryLog();

        $nFlights = count($flightIds);

        DB::transaction(function () use ($NUM_TRIPS, $BATCH_SIZE, $flightIds, $flightPrices, $nFlights) {
            $rows = [];

            for ($i = 0; $i < $NUM_TRIPS; $i++) {
                // Randomly choose trip type (one-way or round-trip)
                $tripType = random_int(0, 1) === 0 ? 'one-way' : 'round-trip';
                
                // Select random flights for this trip
                $selectedFlightIds = [];
                $totalPrice = 0;
                
                if ($tripType === 'one-way') {
                    // Pick one random flight
                    $flightId = $flightIds[random_int(0, $nFlights - 1)];
                    $selectedFlightIds = [$flightId];
                    $totalPrice = $flightPrices[$flightId];
                } else {
                    // Pick two random flights for round-trip
                    $firstIdx = random_int(0, $nFlights - 1);
                    $secondIdx = random_int(0, $nFlights - 1);
                    
                    // Ensure we don't pick the same flight twice
                    while ($secondIdx === $firstIdx) {
                        $secondIdx = random_int(0, $nFlights - 1);
                    }
                    
                    $selectedFlightIds = [
                        $flightIds[$firstIdx],
                        $flightIds[$secondIdx]
                    ];
                    $totalPrice = $flightPrices[$flightIds[$firstIdx]] + $flightPrices[$flightIds[$secondIdx]];
                }

                $rows[] = [
                    'id' => (string) Str::uuid(),
                    'type' => $tripType,
                    'flight_ids' => json_encode($selectedFlightIds),
                    'total_price' => $totalPrice,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Batch insert
                if (count($rows) >= $BATCH_SIZE) {
                    DB::table('trips')->insert($rows);
                    $rows = [];
                }
            }

            if (!empty($rows)) {
                DB::table('trips')->insert($rows);
            }
        });

        $this->command->info("Generated {$NUM_TRIPS} random trips.");
    }
}
