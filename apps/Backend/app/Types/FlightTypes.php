<?php

namespace App\Types;

/**
 * Airline entity
 */
class Airline
{
    public function __construct(
        public string $iataCode,
        public string $name
    ) {}
}

/**
 * Airport entity
 */
class Airport
{
    public function __construct(
        public string $iataCode,
        public string $name,
        public string $city,
        public float $latitude,
        public float $longitude,
        public string $timezone,
        public string $cityCode
    ) {}
}

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
        public string $departureTime,
        public string $arrivalTime,
        public float $price
    ) {}
}

/**
 * Trip entity
 */
class Trip
{
    public function __construct(
        public string $id,
        public string $type,
        public array $flights, // Array of objects with 'flight' and 'departureDate'
        public float $totalPrice,
        public string $createdAt
    ) {}
}
