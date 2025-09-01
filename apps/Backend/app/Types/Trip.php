<?php

namespace App\Types;

/**
 * Trip entity
 */
class Trip
{
    public const TYPE_ONE_WAY = 'one-way';
    public const TYPE_ROUND_TRIP = 'round-trip';
    public const TYPE_MULTI_CITY = 'multi-city';

    public function __construct(
        public string $id,
        public string $type,
        public array $flights, // Array of Flight objects
        public float $totalPrice,
        public string $createdAt
    ) {}
}
