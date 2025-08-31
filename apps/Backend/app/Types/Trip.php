<?php

namespace App\Types;

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
