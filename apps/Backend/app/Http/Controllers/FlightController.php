<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Trip;
use App\Models\Flight;

class FlightController extends Controller
{
    public function search(Request $request)
    {
        // Access query params 
        $params = $request->all();

        // Start with available trips
        $query = Trip::available();

        // Filter by trip type if specified
        if (!empty($params['tripType'])) {
            $query->where('type', $params['tripType']);
        }

        // Get the filtered trips
        $trips = $query->limit(50)->get();

        // Collect all flight IDs from trips
        $allFlightIds = $trips->flatMap(function ($trip) {
            return $trip->flight_ids ?? [];
        })->unique()->values();

        // Load all flights in one query
        $allFlights = Flight::whereIn('id', $allFlightIds)
            ->with(['airline', 'departureAirport', 'arrivalAirport'])
            ->get()
            ->keyBy('id');

        // Filter by flight criteria after loading trips
        $filteredTrips = $trips->filter(function ($trip) use ($params, $allFlights) {
            // Get flights for this trip from the pre-loaded collection
            $flights = collect($trip->flight_ids ?? [])->map(function ($flightId) use ($allFlights) {
                return $allFlights->get($flightId);
            })->filter();
            
            // Filter out departed flights first
            $flights = $flights->filter(function ($flight) {
                $departureDateTime = $flight->departure_date . ' ' . $flight->departure_time;
                return strtotime($departureDateTime) > time();
            });
            
            if ($flights->isEmpty()) {
                return false; // Skip trips with no future flights
            }
            
            // Filter by departure airport
            if (!empty($params['fromAirport'])) {
                $flights = $flights->where('departure_airport', $params['fromAirport']);
            }
            
            // Filter by arrival airport
            if (!empty($params['toAirport'])) {
                $flights = $flights->where('arrival_airport', $params['toAirport']);
            }
            
            // Filter by departure date
            if (!empty($params['departureDate'])) {
                $flights = $flights->where('departure_date', $params['departureDate']);
            }
            
            // Filter by return date (for round-trips)
            if (!empty($params['returnDate']) && $params['tripType'] === 'round-trip') {
                $flights = $flights->where('departure_date', $params['returnDate']);
            }
            
            return $flights->count() > 0;
        });

        // Load relationships for the filtered trips
        $trips = $filteredTrips->map(function ($trip) use ($allFlights) {
            // Get flights for this trip from the pre-loaded collection
            $trip->flights = collect($trip->flight_ids ?? [])->map(function ($flightId) use ($allFlights) {
                return $allFlights->get($flightId);
            })->filter();
            return $trip;
        });

        // Transform trips to match your frontend expectations
        $formattedTrips = $trips->map(function ($trip) {
            return [
                'id' => $trip->id,
                'type' => $trip->type,
                'flights' => $trip->flights->map(function ($flight) {
                    return [
                        'flightNumber' => $flight->flight_number,
                        'airline' => [
                            'iataCode' => $flight->airline->iata_code,
                            'name' => $flight->airline->name,
                        ],
                        'departureAirport' => [
                            'iataCode' => $flight->departure_airport,
                            'name' => $flight->departureAirport->name,
                            'city' => $flight->departureAirport->city,
                            'latitude' => $flight->departureAirport->latitude,
                            'longitude' => $flight->departureAirport->longitude,
                            'timezone' => $flight->departureAirport->timezone,
                            'cityCode' => $flight->departureAirport->city_code,
                        ],
                        'arrivalAirport' => [
                            'iataCode' => $flight->arrival_airport,
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
                'totalPrice' => $trip->total_price,
                'createdAt' => $trip->created_at->toISOString(),
            ];
        });

        return response()->json([
            'status' => 'ok',
            'query'  => $params,
            'flights' => $formattedTrips->values(), // Convert to array
        ]);
    }
}
