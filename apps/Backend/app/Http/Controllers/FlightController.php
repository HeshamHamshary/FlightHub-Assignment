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

        $startTime = microtime(true);
        $tripType = $params['tripType'] ?? 'one-way';

        if ($tripType === 'round-trip') {
            $from = $params['fromAirport'] ?? null;
            $to = $params['toAirport'] ?? null;
            $depDate = $params['departureDate'] ?? null;
            $retDate = $params['returnDate'] ?? null;

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

            $trips = $this->combineRoundTrips($outboundFlights, $returnFlights);
        } else {
            $flights = $this->buildFlightQuery([
                'fromAirport' => $params['fromAirport'] ?? null,
                'toAirport' => $params['toAirport'] ?? null,
                'date' => $params['departureDate'] ?? null,
            ])->get();

            $trips = $this->buildOneWayTrips($flights);
        }

        $formattedTrips = $this->formatTrips($trips);
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);

        return response()->json([
            'status' => 'ok',
            'query'  => $params,
            'flights' => $formattedTrips->values(),
            'meta' => [
                'total' => $trips->count(),
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
        });
    }

    /**
     * Build round-trip combinations from two explicit flight sets
     */
    private function combineRoundTrips($outboundFlights, $returnFlights)
    {
        $trips = collect();

        foreach ($outboundFlights as $outboundFlight) {
            foreach ($returnFlights as $returnFlight) {
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
                        'price' => $flight->price,
                    ];
                })->toArray(),
                'totalPrice' => $trip['totalPrice'],
                'createdAt' => $trip['createdAt'],
            ];
        });
    }
}
