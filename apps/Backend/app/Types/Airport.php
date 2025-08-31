<?php

namespace App\Types;

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
