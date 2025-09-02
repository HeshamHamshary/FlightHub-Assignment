# FlightHub Assignment - Technical Documentation

## Overview

A flight search web application built with Laravel backend and React frontend. Users can search for flights between major airports with filtering options.

## Tech Stack

**Backend:**
- Laravel 12 (PHP)
- PostgreSQL database
- RESTful API endpoints

**Frontend:**
- React 18 + TypeScript
- Modern CSS with responsive design
- Date picker for flight dates

## API Endpoints

### Flight Search
```
GET /api/flight/search
```
Search for available flights with filters:
- `tripType`: one-way or round-trip
- `fromAirport`: departure airport code (e.g., YYZ)
- `toAirport`: arrival airport code (e.g., YUL)
- `departureDate`: YYYY-MM-DD format
- `returnDate`: YYYY-MM-DD format (round-trip only)
- `preferredAirline`: airline code (optional, one-way only)
- `page`: pagination (default: 1)

**Response:**
```json
{
  "status": "ok",
  "flights": [...],
  "meta": {
    "total": 150,
    "page": 1,
    "perPage": 5,
    "totalPages": 30
  }
}
```

### Available Airlines
```
GET /api/flight/search/available-airlines
```
Get airlines that operate on a specific route:
- `fromAirport`: departure airport
- `toAirport`: arrival airport
- `departureDate`: flight date

**Response:**
```json
{
  "airlines": [
    {
      "iataCode": "AC",
      "name": "Air Canada"
    }
  ]
}
```

## Database Schema

### Tables

**flights**
- `id` (UUID, primary key)
- `flight_number` (string)
- `airline_id` (UUID, foreign key)
- `departure_airport` (char(3), foreign key)
- `arrival_airport` (char(3), foreign key)
- `departure_date` (date)
- `departure_time` (string, HH:MM)
- `arrival_time` (string, HH:MM)
- `price` (decimal)

**airlines**
- `id` (UUID, primary key)
- `iata_code` (char(2), unique)
- `name` (string)

**airports**
- `iata_code` (char(3), primary key)
- `name` (string)
- `city` (string)
- `latitude` (decimal)
- `longitude` (decimal)
- `timezone` (string)

### Relationships
- `Flight` belongs to `Airline`
- `Flight` belongs to `Airport` (departure)
- `Flight` belongs to `Airport` (arrival)

## Data Seeders

### AirlineSeeder
- Loads 1,120 airlines from CSV data
- Includes major carriers (AC, AA, DL, UA, WS, BA, AF, LH, etc.)
- IATA codes and full names

### AirportSeeder
- Loads 9,070 airports from CSV data
- Major international airports with coordinates
- IATA codes (YYZ, YUL, JFK, LHR, CDG, etc.)

### FlightSeeder
- Generates 153,860 sample flights
- Realistic routes between major airports
- Sample pricing and schedules
- Future dates only

## Key Features

- **Flight Search**: Find flights between major airports
- **Airline Filtering**: Choose preferred airline for one-way trips
- **Date Selection**: Pick departure and return dates
- **Responsive Design**: Works on desktop and mobile
- **Real-time Results**: Live flight availability
- **Pagination**: Handle large result sets

## Data Sources

- **Airports**: Major international airports (IATA codes)
- **Airlines**: Global airline database with IATA codes
- **Flights**: Generated sample data for demonstration

## Technical Notes

- Uses IATA airport codes (YYZ = Toronto, YUL = Montreal, etc.)
- Flight prices are sample data
- No real-time flight data (demo purposes)
- PostgreSQL compatible (fixed DISTINCT/ORDER BY issues)
- Timezone-aware flight duration calculations
- Major airlines get priority in search results

## Deployment

- **Backend**: DigitalOcean with PostgreSQL
- **Frontend**: Vercel
- **Database**: PostgreSQL hosted on DigitalOcean
- **Environment**: Production with proper error handling

## Performance

- Database queries optimized with proper indexing
- Pagination for large result sets
- Efficient airline filtering
- Cached configuration values
