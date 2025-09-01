<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Airline extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id','iata_code','name'];

    /**
     * Get the flights for this airline
     */
    public function flights()
    {
        return $this->hasMany(Flight::class, 'airline_id');
    }
}

