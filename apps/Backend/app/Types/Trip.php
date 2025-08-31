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
        public array $flights, // Array of Flight objects
        public float $totalPrice,
        public string $createdAt
    ) {}
}
