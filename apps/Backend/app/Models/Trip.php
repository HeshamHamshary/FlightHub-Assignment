<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Trip extends Model
{
    use HasUuids;

    protected $fillable = [
        'type',
        'flight_ids',
        'total_price',
    ];

    protected $casts = [
        'flight_ids' => 'array',
        'total_price' => 'decimal:2',
    ];

    /**
     * Get the flights for this trip
     */
    public function getFlightsAttribute()
    {
        if (empty($this->flight_ids)) {
            return collect();
        }
        
        return Flight::whereIn('id', $this->flight_ids)->get();
    }

    /**
     * Set the flight IDs and ensure they're stored as an array
     */
    public function setFlightIdsAttribute($value)
    {
        $this->attributes['flight_ids'] = is_array($value) ? json_encode($value) : $value;
    }
}
