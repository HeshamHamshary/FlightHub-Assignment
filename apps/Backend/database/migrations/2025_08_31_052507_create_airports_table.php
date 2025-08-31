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
        Schema::create('airports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->char('iata_code', 3)->nullable()->index();
            $table->string('name', 160);
            $table->string('city', 120)->nullable();
            $table->string('country', 2)->nullable(); // ISO-3166 alpha-2
            $table->decimal('lat', 9, 6)->nullable();
            $table->decimal('lon', 9, 6)->nullable();
            $table->string('timezone', 64)->nullable();
            $table->timestamps();

            $table->index(['name', 'city']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('airports');
    }
};
