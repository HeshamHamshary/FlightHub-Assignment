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

        $BATCH_SIZE = 300; // Smaller batch size for better memory management
        // Get major airports from constants
        $majorAirports = config('constants.major_airports');
        
        if (empty($majorAirports)) {
            $this->command->warn("Major airports not found in constants. Using fallback airports.");
            $majorAirports = [
                'YUL' => 'Montreal-Trudeau',
                'YYZ' => 'Toronto-Pearson',
                'JFK' => 'New York-JFK',
                'LHR' => 'London-Heathrow',
                'CDG' => 'Paris-Charles de Gaulle',
                'NRT' => 'Tokyo-Narita',
                'DCA' => 'Washington-Reagan',
                'LAX' => 'Los Angeles',
            ];
        }

        // Get airlines
        $airlines = Airline::whereNotNull('iata_code')->get(['id', 'iata_code'])->toArray();
        
        if (empty($airlines)) {
            $this->command->warn("No airlines found. Cannot seed flights.");
            return;
        }

        // Calculate date range: from today to January 1st, 2026
        $startDate = now()->startOfDay();
        $endDate = \Carbon\Carbon::create(2026, 1, 1)->endOfDay();
        $totalDays = $startDate->diffInDays($endDate) + 1;

        DB::disableQueryLog();

        // Use transaction for data integrity
        DB::transaction(function () use ($majorAirports, $airlines, $startDate, $endDate, $totalDays, $BATCH_SIZE) {
            $rows = [];
            $totalFlights = 0;
            $currentDate = $startDate->copy();
            $processedDays = 0;

            while ($currentDate->lte($endDate)) {
                $dateString = $currentDate->format('Y-m-d');
                $processedDays++;
                
                // Calculate and log percentage complete
                $percentageComplete = round(($processedDays / $totalDays) * 100, 1);
                $this->command->info("Processing ({$percentageComplete}% complete)");
                
                // Generate flights between all major airports for this date
                foreach (array_keys($majorAirports) as $fromAirport) {
                    foreach (array_keys($majorAirports) as $toAirport) {
                        // Skip same airport
                        if ($fromAirport === $toAirport) {
                            continue;
                        }

                        // Generate 20-25 flights per route per day to ensure minimum 20 search results
                        $flightsPerRoute = random_int(20, 25);
                        
                        for ($i = 0; $i < $flightsPerRoute; $i++) {
                            $airline = $airlines[random_int(0, count($airlines) - 1)];
                            
                            // Generate departure time between 06:00 and 22:00
                            $departureHour = random_int(6, 22);
                            $departureMinute = random_int(0, 59);
                            $departureTime = sprintf('%02d:%02d', $departureHour, $departureMinute);
                            
                            // Flight duration between 1-12 hours
                            $durationHours = random_int(1, 12);
                            $durationMinutes = random_int(0, 59);
                            
                            // Calculate arrival time
                            $departureDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $dateString . ' ' . $departureTime);
                            $arrivalDateTime = $departureDateTime->copy()->addHours($durationHours)->addMinutes($durationMinutes);
                            $arrivalTime = $arrivalDateTime->format('H:i');
                            
                            // Price based on duration and distance (simplified)
                            $basePrice = 100;
                            $durationMultiplier = $durationHours * 15;
                            $randomVariation = random_int(-20, 50);
                            $price = round($basePrice + $durationMultiplier + $randomVariation, 2);
                            
                            $rows[] = [
                                'id' => (string) Str::uuid(),
                                'flight_number' => $airline['iata_code'] . random_int(100, 999),
                                'airline_id' => $airline['id'],
                                'departure_airport' => $fromAirport,
                                'arrival_airport' => $toAirport,
                                'departure_date' => $dateString,
                                'departure_time' => $departureTime,
                                'arrival_time' => $arrivalTime,
                                'price' => $price,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                            
                            $totalFlights++;
                            
                            // Batch insert when we reach batch size
                            if (count($rows) >= $BATCH_SIZE) {
                                DB::table('flights')->insert($rows);
                                $rows = [];
                                
                                // More frequent memory cleanup
                                if ($totalFlights % 2000 === 0) {
                                    gc_collect_cycles();
                                }
                            }
                        }
                    }
                }
                
                $currentDate->addDay();
                
                // Memory cleanup after each day
                if ($processedDays % 5 === 0) {
                    gc_collect_cycles();
                }
            }
            
            // Insert remaining rows
            if (!empty($rows)) {
                DB::table('flights')->insert($rows);
                $totalFlights += count($rows);
            }
            
            $this->command->info("Flight seeding completed! Total flights generated: {$totalFlights}");
            
            // Calculate expected trips
            $routesPerDay = count($majorAirports) * (count($majorAirports) - 1); // Exclude same airport
            $totalRoutes = $routesPerDay * $totalDays;
        });
    }
}
