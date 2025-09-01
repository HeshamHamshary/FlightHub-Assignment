<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Flight;
use App\Models\Airline;

class FlightController extends Controller
{
    public function search(Request $request)
    {
        // Access query params 
        $params = $request->all();

        $startTime = microtime(true);
        $tripType = $params['tripType'] ?? 'one-way';
        
        // Pagination parameters
        $page = max(1, (int)($params['page'] ?? 1));
        $perPage = 5;
        $offset = ($page - 1) * $perPage;

        if ($tripType === 'round-trip') {
            $from = $params['fromAirport'] ?? null;
            $to = $params['toAirport'] ?? null;
            $depDate = $params['departureDate'] ?? null;
            $retDate = $params['returnDate'] ?? null;

            // Get all outbound and return flights (no airline filtering at query level)
            $outboundFlights = $this->buildFlightQuery([
                'fromAirport' => $from,
                'toAirport' => $to,
                'date' => $depDate,
            ])->get();

            $returnFlights = $this->buildFlightQuery([
                'fromAirport' => $to,
                'toAirport' => $from,
                'date' => $retDate,
            ])->get();

            $preferredAirline = $params['preferredAirline'] ?? null;
            $trips = $this->combineRoundTrips($outboundFlights, $returnFlights, $preferredAirline);
        } else {
            $flights = $this->buildFlightQuery([
                'fromAirport' => $params['fromAirport'] ?? null,
                'toAirport' => $params['toAirport'] ?? null,
                'date' => $params['departureDate'] ?? null,
                'preferredAirline' => $params['preferredAirline'] ?? null,
            ])->get();

            $trips = $this->buildOneWayTrips($flights);
        }

        // Apply pagination
        $totalTrips = $trips->count();
        $paginatedTrips = $trips->slice($offset, $perPage);
        $formattedTrips = $this->formatTrips($paginatedTrips);
        
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);

        return response()->json([
            'status' => 'ok',
            'query'  => $params,
            'flights' => $formattedTrips->values(),
            'meta' => [
                'total' => $totalTrips,
                'page' => $page,
                'perPage' => $perPage,
                'totalPages' => ceil($totalTrips / $perPage),
                'hasNextPage' => $page < ceil($totalTrips / $perPage),
                'hasPreviousPage' => $page > 1,
                'executionTimeMs' => $executionTime,
            ]
        ]);
    }

    /**
     * Build one-way trips from individual flights
     */
    private function buildOneWayTrips($flights)
    {
        return $flights->map(function ($flight) {
            return [
                'id' => 'one-way-' . $flight->id,
                'type' => 'one-way',
                'flights' => collect([$flight]),
                'totalPrice' => $flight->price,
                'createdAt' => $flight->created_at->toISOString(),
            ];
        })->sortBy('totalPrice');
    }

    /**
     * Build round-trip combinations from two explicit flight sets
     * Filters to only include trips where either flight matches preferred airline
     */
    private function combineRoundTrips($outboundFlights, $returnFlights, $preferredAirline = null)
    {
        $trips = collect();

        foreach ($outboundFlights as $outboundFlight) {
            foreach ($returnFlights as $returnFlight) {
                $outboundDateTime = $outboundFlight->departure_date . ' ' . $outboundFlight->departure_time;
                $returnDateTime = $returnFlight->departure_date . ' ' . $returnFlight->departure_time;

                if (strtotime($returnDateTime) > strtotime($outboundDateTime)) {
                    // If preferred airline is specified, filter to only include trips with at least one matching flight
                    if ($preferredAirline) {
                        $hasPreferredAirline = ($outboundFlight->airline->iata_code === $preferredAirline || 
                                              $returnFlight->airline->iata_code === $preferredAirline);
                        
                        // Skip this combination if neither flight matches preferred airline
                        if (!$hasPreferredAirline) {
                            continue;
                        }
                    }
                    
                    $trips->push([
                        'id' => 'round-' . $outboundFlight->id . '-' . $returnFlight->id,
                        'type' => 'round-trip',
                        'flights' => collect([$outboundFlight, $returnFlight]),
                        'totalPrice' => $outboundFlight->price + $returnFlight->price,
                        'createdAt' => now()->toISOString(),
                    ]);
                }
            }
        }

        return $trips->sortBy('totalPrice');
    }

    /**
     * Build base query and apply optional filters
     */
    private function buildFlightQuery(array $filters)
    {
        $query = Flight::where('departure_date', '>=', now()->format('Y-m-d'))
            ->whereRaw("CONCAT(departure_date, ' ', departure_time) > ?", [now()])
            ->with(['airline', 'departureAirport', 'arrivalAirport']);

        if (!empty($filters['fromAirport'])) {
            $query->where('departure_airport', $filters['fromAirport']);
        }
        if (!empty($filters['toAirport'])) {
            $query->where('arrival_airport', $filters['toAirport']);
        }
        if (!empty($filters['date'])) {
            $query->where('departure_date', $filters['date']);
        }
        if (!empty($filters['preferredAirline'])) {
            $query->whereHas('airline', function ($q) use ($filters) {
                $q->where('iata_code', $filters['preferredAirline']);
            });
        }

        return $query;
    }

    /**
     * Format trips to match frontend expectations
     */
    private function formatTrips($trips)
    {
        return $trips->map(function ($trip) {
            return [
                'id' => $trip['id'],
                'type' => $trip['type'],
                'flights' => $trip['flights']->map(function ($flight) {
                    return [
                        'flightNumber' => $flight->flight_number,
                        'airline' => [
                            'iataCode' => $flight->airline->iata_code,
                            'name' => $flight->airline->name,
                        ],
                        'departureAirport' => [
                            'iataCode' => $flight->departureAirport->iata_code,
                            'name' => $flight->departureAirport->name,
                            'city' => $flight->departureAirport->city,
                            'latitude' => $flight->departureAirport->latitude,
                            'longitude' => $flight->departureAirport->longitude,
                            'timezone' => $flight->departureAirport->timezone,
                            'cityCode' => $flight->departureAirport->city_code,
                        ],
                        'arrivalAirport' => [
                            'iataCode' => $flight->arrivalAirport->iata_code,
                            'name' => $flight->arrivalAirport->name,
                            'city' => $flight->arrivalAirport->city,
                            'latitude' => $flight->arrivalAirport->latitude,
                            'longitude' => $flight->arrivalAirport->longitude,
                            'timezone' => $flight->arrivalAirport->timezone,
                            'cityCode' => $flight->arrivalAirport->city_code,
                        ],
                        'departureDate' => $flight->departure_date,
                        'departureTime' => $flight->departure_time,
                        'arrivalTime' => $flight->arrival_time,
                        'duration' => $this->calculateFlightDuration($flight),
                        'price' => $flight->price,
                    ];
                })->toArray(),
                'totalPrice' => $trip['totalPrice'],
                'createdAt' => $trip['createdAt'],
            ];
        });
    }

    /**
     * Calculate flight duration with timezone awareness
     */
    private function calculateFlightDuration($flight)
    {
        try {
            // Create departure datetime in departure timezone
            $departureDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', 
                $flight->departure_date . ' ' . $flight->departure_time,
                $flight->departureAirport->timezone
            );

            // Create arrival datetime in arrival timezone (same date initially)
            $arrivalDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i',
                $flight->departure_date . ' ' . $flight->arrival_time,
                $flight->arrivalAirport->timezone
            );

            // Convert both to UTC to calculate actual duration
            $departureUTC = $departureDateTime->utc();
            $arrivalUTC = $arrivalDateTime->utc();

            // Handle overnight flights (arrival next day)
            if ($arrivalUTC->lte($departureUTC)) {
                $arrivalUTC->addDay();
            }

            // Calculate duration in minutes
            $durationMinutes = $departureUTC->diffInMinutes($arrivalUTC);
            
            // Convert to hours and minutes
            $hours = intval($durationMinutes / 60);
            $minutes = $durationMinutes % 60;

            return $hours . 'h ' . $minutes . 'm';
        } catch (\Exception $e) {
            Log::warning('Duration calculation failed for flight ' . $flight->flight_number . ': ' . $e->getMessage());
            return 'N/A';
        }
    }



    /**
     * Get available airlines for search criteria
     */
    public function getAvailableAirlines(Request $request)
    {
        try {
            // Build flight query conditions
            $conditions = [['departure_date', '>=', now()->format('Y-m-d')]];
            foreach (['fromAirport' => 'departure_airport', 'toAirport' => 'arrival_airport', 'departureDate' => 'departure_date'] as $param => $column) {
                if ($value = $request->get($param)) $conditions[] = [$column, '=', $value];
            }
            
            // Get airlines with matching flights, prioritize major airlines
            $airlines = DB::table('flights')
                ->join('airlines', 'flights.airline_id', '=', 'airlines.id')
                ->where($conditions)
                ->whereRaw("CONCAT(flights.departure_date, ' ', flights.departure_time) > ?", [now()])
                ->select('airlines.iata_code as iataCode', 'airlines.name')
                ->distinct()
                ->orderByRaw("CASE WHEN airlines.iata_code IN ('" . implode("','", array_keys(config('constants.major_airlines'))) . "') THEN 0 ELSE 1 END")
                ->orderBy('airlines.name')
                ->limit(15)
                ->get();

            // Fallback if no results
            if ($airlines->isEmpty()) {
                $airlines = DB::table('airlines')
                    ->whereIn('iata_code', array_keys(config('constants.major_airlines')))
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))->from('flights')->whereColumn('flights.airline_id', 'airlines.id');
                    })
                    ->select('iata_code as iataCode', 'name')
                    ->orderBy('name')
                    ->limit(10)
                    ->get();
            }

            return response()->json(['airlines' => $airlines]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch available airlines: ' . $e->getMessage());
            return response()->json(['airlines' => []], 500);
        }
    }
}
