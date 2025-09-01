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

        $BATCH_SIZE = 1000; // Larger batch size for better performance
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

        // Pre-load airport timezone data to avoid repeated queries
        $airportTimezones = Airport::whereIn('iata_code', array_keys($majorAirports))
            ->pluck('timezone', 'iata_code')
            ->toArray();

        // Calculate date range: from today to January 1st, 2026
        $startDate = now()->startOfDay();
        $endDate = \Carbon\Carbon::create(2026, 1, 1)->endOfDay();
        $totalDays = $startDate->diffInDays($endDate) + 1;

        DB::disableQueryLog();

        // Use transaction for data integrity
        DB::transaction(function () use ($majorAirports, $airlines, $airportTimezones, $startDate, $endDate, $totalDays, $BATCH_SIZE) {
            $rows = [];
            $totalFlights = 0;
            $currentDate = $startDate->copy();
            $processedDays = 0;

            while ($currentDate->lte($endDate)) {
                $dateString = $currentDate->format('Y-m-d');
                $processedDays++;
                
                // Log progress less frequently
                if ($processedDays % 10 === 0 || $processedDays === 1) {
                    $percentageComplete = round(($processedDays / $totalDays) * 100, 1);
                    $this->command->info("Processing ({$percentageComplete}% complete)");
                }
                
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
                            
                            // Get timezone information from pre-loaded data
                            $departureTimezone = $airportTimezones[$fromAirport] ?? 'UTC';
                            $arrivalTimezone = $airportTimezones[$toAirport] ?? 'UTC';
                            
                            // Generate departure time between 06:00 and 22:00 in departure timezone
                            $departureHour = random_int(6, 22);
                            $departureMinute = random_int(0, 59);
                            $departureTime = sprintf('%02d:%02d', $departureHour, $departureMinute);
                            
                            // Create departure datetime in departure timezone
                            $departureDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $dateString . ' ' . $departureTime, $departureTimezone);
                            
                            // Calculate realistic flight duration based on route (simplified)
                            $durationHours = $this->getFlightDuration($fromAirport, $toAirport);
                            $durationMinutes = random_int(0, 59);
                            
                            // Calculate arrival time in arrival timezone
                            $arrivalDateTime = $departureDateTime->copy()
                                ->addHours($durationHours)
                                ->addMinutes($durationMinutes)
                                ->setTimezone($arrivalTimezone);
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
                                
                                // Less frequent memory cleanup
                                if ($totalFlights % 5000 === 0) {
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

    /**
     * Get realistic flight duration in hours based on route
     */
    private function getFlightDuration($fromAirport, $toAirport)
    {
        // Simple duration mapping based on common routes
        $durations = [
            // Domestic North America (shorter flights)
            'YUL-YYZ' => 1, 'YYZ-YUL' => 1,
            'YUL-DCA' => 2, 'DCA-YUL' => 2,
            'YYZ-DCA' => 2, 'DCA-YYZ' => 2,
            'YYZ-LAX' => 5, 'LAX-YYZ' => 5,
            'YUL-LAX' => 5, 'LAX-YUL' => 5,
            'DCA-LAX' => 5, 'LAX-DCA' => 5,
            
            // Transatlantic (medium flights)
            'YUL-LHR' => 7, 'LHR-YUL' => 8,
            'YYZ-LHR' => 8, 'LHR-YYZ' => 9,
            'DCA-LHR' => 8, 'LHR-DCA' => 9,
            'LAX-LHR' => 11, 'LHR-LAX' => 11,
            'YUL-CDG' => 7, 'CDG-YUL' => 8,
            'YYZ-CDG' => 8, 'CDG-YYZ' => 9,
            'DCA-CDG' => 8, 'CDG-DCA' => 9,
            'LAX-CDG' => 12, 'CDG-LAX' => 12,
            
            // Transpacific (long flights)
            'YUL-NRT' => 13, 'NRT-YUL' => 12,
            'YYZ-NRT' => 13, 'NRT-YYZ' => 12,
            'DCA-NRT' => 14, 'NRT-DCA' => 13,
            'LAX-NRT' => 11, 'NRT-LAX' => 10,
            
            // Europe-Asia
            'LHR-NRT' => 12, 'NRT-LHR' => 11,
            'CDG-NRT' => 12, 'NRT-CDG' => 11,
        ];

        $route = $fromAirport . '-' . $toAirport;
        return $durations[$route] ?? random_int(2, 8); // Default fallback
    }
}
