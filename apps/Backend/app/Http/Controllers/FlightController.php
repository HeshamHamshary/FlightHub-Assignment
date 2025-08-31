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
        // Access query params like ?num_adults=1
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
        $outboundFlight1 = new Flight('301', $mockAirline, $mockYUL, $mockPEK, '07:30', '16:10', 841.39);
        $returnFlight1 = new Flight('302', $mockAirline, $mockPEK, $mockYUL, '18:05', '00:36', 512.30);
        $flight2 = new Flight('401', $mockWestJet, $mockYYZ, $mockLAX, '09:15', '12:45', 325.50);
        $outboundFlight3 = new Flight('501', $mockWestJet, $mockYVR, $mockYYZ, '14:20', '21:45', 298.75);
        $returnFlight3 = new Flight('502', $mockWestJet, $mockYYZ, $mockYVR, '08:30', '15:55', 298.75);
        $flight4 = new Flight('601', $mockAirline, $mockYUL, $mockYVR, '11:45', '14:20', 456.80);

        // Mock trips
        $trips = [
            new Trip(
                '1',
                'one-way',
                [
                    [
                        'flight' => $outboundFlight1,
                        'departureDate' => '2024-01-15'
                    ]
                ],
                841.39,
                now()->toISOString()
            ),
            new Trip(
                '2',
                'round-trip',
                [
                    [
                        'flight' => $outboundFlight1,
                        'departureDate' => '2024-01-15'
                    ],
                    [
                        'flight' => $returnFlight1,
                        'departureDate' => '2024-01-22'
                    ]
                ],
                1353.69,
                now()->toISOString()
            ),
            new Trip(
                '3',
                'one-way',
                [
                    [
                        'flight' => $flight2,
                        'departureDate' => '2024-01-20'
                    ]
                ],
                325.50,
                now()->toISOString()
            ),
            new Trip(
                '4',
                'round-trip',
                [
                    [
                        'flight' => $outboundFlight3,
                        'departureDate' => '2024-01-18'
                    ],
                    [
                        'flight' => $returnFlight3,
                        'departureDate' => '2024-01-25'
                    ]
                ],
                597.50,
                now()->toISOString()
            ),
            new Trip(
                '5',
                'one-way',
                [
                    [
                        'flight' => $flight4,
                        'departureDate' => '2024-01-12'
                    ]
                ],
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
