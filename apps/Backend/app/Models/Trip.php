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
     * Get the flights relationship for this trip
     */

    public function flights()
    {
        return Flight::whereIn('id', $this->flight_ids ?? []);
    }


    /**
     * Get the flights for this trip (accessor for backward compatibility)
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

    /**
     * Check if any flight in this trip has departed
     */
    public function hasDeparted(): bool
    {
        $flights = $this->flights;
        foreach ($flights as $flight) {
            $departureDateTime = $flight->departure_date . ' ' . $flight->departure_time;
            if (strtotime($departureDateTime) < time()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Scope to only include trips that haven't departed yet
     */
    public function scopeAvailable($query)
    {
        // For now, return all trips and filter in PHP
        // This avoids SQLite JSON issues
        return $query;
    }
}
