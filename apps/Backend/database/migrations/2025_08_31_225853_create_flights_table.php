<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('flights', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Flight number like "AC301"
            $table->string('flight_number');

            // Airline reference
            $table->uuid('airline_id');
            $table->foreign('airline_id')
                  ->references('id')
                  ->on('airlines')
                  ->cascadeOnDelete();

            // Departure airport reference
            $table->char('departure_airport', 3);
            $table->foreign('departure_airport')
                  ->references('iata_code')
                  ->on('airports')
                  ->cascadeOnDelete();

            // Arrival airport reference
            $table->char('arrival_airport', 3);
            $table->foreign('arrival_airport')
                  ->references('iata_code')
                  ->on('airports')
                  ->cascadeOnDelete();

            // Times and dates (local times in string format, but could also be proper datetime)
            $table->date('departure_date');
            $table->string('departure_time'); // HH:MM local time
            $table->string('arrival_time');   // HH:MM local time

            // Price
            $table->decimal('price', 8, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flights');
    }
};
