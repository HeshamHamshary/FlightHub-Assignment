<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Flight extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id','flight_number','airline_id','departure_airport','arrival_airport','departure_date','departure_time','arrival_time','price'];

    /**
     * Get the airline that owns the flight
     */
    public function airline()
    {
        return $this->belongsTo(Airline::class, 'airline_id');
    }

    /**
     * Get the departure airport
     */
    public function departureAirport()
    {
        return $this->belongsTo(Airport::class, 'departure_airport', 'iata_code');
    }

    /**
     * Get the arrival airport
     */
    public function arrivalAirport()
    {
        return $this->belongsTo(Airport::class, 'arrival_airport', 'iata_code');
    }
}

