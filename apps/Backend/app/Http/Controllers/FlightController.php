<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Types\Airline;
use App\Types\Airport;
use App\Types\Flight;
use App\Types\Trip;

class FlightController extends Controller
{
    public function search(Request $request)
    {
        // Access query params 
        $params = $request->all();

        // Mock data
        $mockAirline = new Airline('AC', 'Air Canada');
        $mockWestJet = new Airline('WS', 'WestJet');

        $mockYUL = new Airport('YUL', 'Pierre Elliott Trudeau International', 'Montreal', 45.4706, -73.7408, 'America/Montreal', 'YMQ');
        $mockPEK = new Airport('PEK', 'Beijing Capital International', 'Beijing', 40.0799, 116.6031, 'Asia/Shanghai', 'BJS');
        $mockYVR = new Airport('YVR', 'Vancouver International', 'Vancouver', 49.1967, -123.1815, 'America/Vancouver', 'YVR');
        $mockYYZ = new Airport('YYZ', 'Toronto Pearson International', 'Toronto', 43.6777, -79.6248, 'America/Toronto', 'YYZ');
        $mockLAX = new Airport('LAX', 'Los Angeles International', 'Los Angeles', 33.9416, -118.4085, 'America/Los_Angeles', 'LAX');

        // Mock flights
        $outboundFlight1 = new Flight('301', $mockAirline, $mockYUL, $mockPEK, '2024-01-15', '07:30', '16:10', 841.39);
        $returnFlight1 = new Flight('302', $mockAirline, $mockPEK, $mockYUL, '2024-01-22', '18:05', '00:36', 512.30);
        $flight2 = new Flight('401', $mockWestJet, $mockYYZ, $mockLAX, '2024-01-20', '09:15', '12:45', 325.50);
        $outboundFlight3 = new Flight('501', $mockWestJet, $mockYVR, $mockYYZ, '2024-01-18', '14:20', '21:45', 298.75);
        $returnFlight3 = new Flight('502', $mockWestJet, $mockYYZ, $mockYVR, '2024-01-25', '08:30', '15:55', 298.75);
        $flight4 = new Flight('601', $mockAirline, $mockYUL, $mockYVR, '2024-01-12', '11:45', '14:20', 456.80);

        // Mock trips
        $trips = [
            new Trip(
                '1',
                'one-way',
                [$outboundFlight1],
                841.39,
                now()->toISOString()
            ),
            new Trip(
                '2',
                'round-trip',
                [$outboundFlight1, $returnFlight1],
                1353.69,
                now()->toISOString()
            ),
            new Trip(
                '3',
                'one-way',
                [$flight2],
                325.50,
                now()->toISOString()
            ),
            new Trip(
                '4',
                'round-trip',
                [$outboundFlight3, $returnFlight3],
                597.50,
                now()->toISOString()
            ),
            new Trip(
                '5',
                'one-way',
                [$flight4],
                456.80,
                now()->toISOString()
            )
        ];

        return response()->json([
            'status' => 'ok',
            'query'  => $params,
            'flights' => $trips,
        ]);
    }
}
