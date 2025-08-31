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
