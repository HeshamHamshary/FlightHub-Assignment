<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Flight;

class FlightController extends Controller
{
    public function search(Request $request)
    {
        // Access query params 
        $params = $request->all();

        // Start with available flights (not departed)
        $query = Flight::where('departure_date', '>=', now()->format('Y-m-d'))
            ->whereRaw("CONCAT(departure_date, ' ', departure_time) > ?", [now()])
            ->with(['airline', 'departureAirport', 'arrivalAirport']);

        // Filter by departure airport using airport IATA code
        if (!empty($params['fromAirport'])) {
            $query->where('departure_airport', $params['fromAirport']);
        }
        
        // Filter by arrival airport using airport IATA code
        if (!empty($params['toAirport'])) {
            $query->where('arrival_airport', $params['toAirport']);
        }
        
        // Filter by departure date
        if (!empty($params['departureDate'])) {
            $query->where('departure_date', $params['departureDate']);
        }

        // Group flights by trip type
        $tripType = $params['tripType'] ?? 'one-way';
        
        // Process flights in chunks to avoid memory issues
        $allTrips = collect();
        $chunkSize = 1000; // Process 1000 flights at a time
        $startTime = microtime(true);
        $maxExecutionTime = 25; // Leave 5 seconds buffer for response formatting
        
        $query->chunk($chunkSize, function ($flights) use (&$allTrips, $tripType, $params, $startTime, $maxExecutionTime) {
            // Check if we're approaching time limit
            if ((microtime(true) - $startTime) > $maxExecutionTime) {
                return false; // Stop chunking
            }
            
            if ($tripType === 'round-trip') {
                $chunkTrips = $this->buildRoundTrips($flights, $params);
            } else {
                $chunkTrips = $this->buildOneWayTrips($flights);
            }
            
            $allTrips = $allTrips->merge($chunkTrips);
        });

        // Transform trips to match your frontend expectations
        $formattedTrips = $allTrips->map(function ($trip) {
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
                        'price' => $flight->price,
                    ];
                })->toArray(),
                'totalPrice' => $trip['totalPrice'],
                'createdAt' => $trip['createdAt'],
            ];
        });

        $executionTime = round((microtime(true) - $startTime) * 1000, 2);

        return response()->json([
            'status' => 'ok',
            'query'  => $params,
            'flights' => $formattedTrips->values(),
            'meta' => [
                'total' => $allTrips->count(),
                'executionTimeMs' => $executionTime,
                'chunkSize' => $chunkSize,
                'timeLimitReached' => $executionTime > ($maxExecutionTime * 1000)
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
        });
    }

    /**
     * Build round-trip combinations from available flights
     */
    private function buildRoundTrips($flights, $params)
    {
        $trips = collect();
        
        // Group flights by direction
        $outboundFlights = $flights->filter(function ($flight) use ($params) {
            return $flight->departure_airport === ($params['fromAirport'] ?? $flight->departure_airport) &&
                   $flight->arrival_airport === ($params['toAirport'] ?? $flight->arrival_airport);
        });
        
        $returnFlights = $flights->filter(function ($flight) use ($params) {
            return $flight->departure_airport === ($params['toAirport'] ?? $flight->arrival_airport) &&
                   $flight->arrival_airport === ($params['fromAirport'] ?? $flight->departure_airport);
        });

        // Create round-trip combinations
        foreach ($outboundFlights as $outboundFlight) {
            foreach ($returnFlights as $returnFlight) {
                // Ensure return flight is after outbound flight
                $outboundDateTime = $outboundFlight->departure_date . ' ' . $outboundFlight->departure_time;
                $returnDateTime = $returnFlight->departure_date . ' ' . $returnFlight->departure_time;
                
                if (strtotime($returnDateTime) > strtotime($outboundDateTime)) {
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

        return $trips;
    }
}
