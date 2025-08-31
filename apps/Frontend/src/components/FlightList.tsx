import FlightCard from './FlightCard'
import { type Trip, type Flight, type Airline, type Airport } from '../types/flightTypes'

// Main FlightList component
function FlightList() {

  // Mock data
  const mockAirline: Airline = {
    iataCode: 'AC',
    name: 'Air Canada'
  }

  const mockYUL: Airport = {
    iataCode: 'YUL',
    name: 'Pierre Elliott Trudeau International',
    city: 'Montreal',
    latitude: 45.4706,
    longitude: -73.7408,
    timezone: 'America/Montreal',
    cityCode: 'YMQ'
  }

  const mockPEK: Airport = {
    iataCode: 'PEK',
    name: 'Beijing Capital International',
    city: 'Beijing',
    latitude: 40.0799,
    longitude: 116.6031,
    timezone: 'Asia/Shanghai',
    cityCode: 'BJS'
  }

  const mockYVR: Airport = {
    iataCode: 'YVR',
    name: 'Vancouver International',
    city: 'Vancouver',
    latitude: 49.1967,
    longitude: -123.1815,
    timezone: 'America/Vancouver',
    cityCode: 'YVR'
  }

  // Mock flights
  const outboundFlight: Flight = {
    flightNumber: '301',
    airline: mockAirline,
    departureAirport: mockYUL,
    arrivalAirport: mockPEK,
    departureTime: '07:30',
    arrivalTime: '16:10',
    price: 841.39,
  }

  const returnFlight: Flight = {
    flightNumber: '302',
    airline: mockAirline,
    departureAirport: mockPEK,
    arrivalAirport: mockYUL,
    departureTime: '18:05',
    arrivalTime: '00:36',
    price: 512.30,
  }

  // Mock trips
  const trips: Trip[] = [
    {
      id: '1',
      type: 'one-way',
      flights: [
        {
          flight: outboundFlight,
          departureDate: '2024-01-15'
        }
      ],
      totalPrice: 841.39,
      createdAt: new Date().toISOString()
    },
    {
      id: '2',
      type: 'round-trip',
      flights: [
        {
          flight: outboundFlight,
          departureDate: '2024-01-15'
        },
        {
          flight: returnFlight,
          departureDate: '2024-01-22'
        }
      ],
      totalPrice: 1353.69,
      createdAt: new Date().toISOString()
    }
  ]

  // Sort trips by total price (lowest first)
  const sortedTrips = [...trips].sort((a, b) => a.totalPrice - b.totalPrice)

  return (
    <div className="flight-list-container">
      {/* Header */}
      <div className="flight-list-header">
        <h2>Flight Results</h2>
      </div>

      {/* Flight cards */}
      <div className="flight-cards">
        {sortedTrips.map((trip) => (
          <FlightCard
            key={trip.id}
            trip={trip}
          />
        ))}
      </div>
    </div>
  )
}

export default FlightList
