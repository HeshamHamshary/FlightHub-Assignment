<?php

namespace App\Types;

/**
 * Flight entity
 */
class Flight
{
    public function __construct(
        public string $flightNumber,
        public Airline $airline,
        public Airport $departureAirport,
        public Airport $arrivalAirport,
        public string $departureDate,
        public string $departureTime,
        public string $arrivalTime,
        public float $price
    ) {}
}
