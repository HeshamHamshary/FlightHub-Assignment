/*
 * ================================================================
 * FLIGHT LIST COMPONENT TESTS
 * ================================================================
 * This file tests the FlightList component functionality:
 * - Loading states
 * - Empty state display
 * - Flight cards rendering
 * - Conditional rendering based on props
 * ================================================================
 */

import { render, screen } from '@testing-library/react'
import FlightList from '../FlightList'
import { type Trip } from '../../types/flightTypes'

/*
 * ================================================================
 * MOCKS - External Dependencies
 * ================================================================
 */

// Mock FlightCard component to simplify testing
jest.mock('../FlightCard', () => {
  return function MockFlightCard({ trip }: { trip: any }) {
    return (
      <div data-testid="flight-card">
        <span>Flight: {trip.id}</span>
        <span>Price: ${trip.totalPrice}</span>
      </div>
    )
  }
})

// Mock the airplane SVG import
jest.mock('../../public/airplane.svg', () => 'airplane-icon.svg')

/*
 * ================================================================
 * TEST DATA - Sample Trips
 * ================================================================
 */

const mockTrip1: Trip = {
  id: 'trip-1',
  type: 'one-way',
  flights: [
    {
      flightNumber: 'AC123',
      airline: { iataCode: 'AC', name: 'Air Canada' },
      departureAirport: {
        iataCode: 'YUL',
        name: 'Montreal Trudeau',
        city: 'Montreal',
        latitude: 45.4706,
        longitude: -73.7408,
        timezone: 'America/Toronto',
        cityCode: 'YMQ'
      },
      arrivalAirport: {
        iataCode: 'YYZ',
        name: 'Toronto Pearson',
        city: 'Toronto',
        latitude: 43.6777,
        longitude: -79.6248,
        timezone: 'America/Toronto',
        cityCode: 'YTO'
      },
      departureDate: '2025-10-03',
      departureTime: '08:00',
      arrivalTime: '09:30',
      price: 299.99
    }
  ],
  totalPrice: 299.99,
  createdAt: '2025-01-15T10:00:00Z'
}

const mockTrip2: Trip = {
  id: 'trip-2',
  type: 'round-trip',
  flights: [
    // Outbound flight: YVR -> LAX
    {
      flightNumber: 'WJ456',
      airline: { iataCode: 'WJ', name: 'WestJet' },
      departureAirport: {
        iataCode: 'YVR',
        name: 'Vancouver International',
        city: 'Vancouver',
        latitude: 49.1939,
        longitude: -123.1844,
        timezone: 'America/Vancouver',
        cityCode: 'YVR'
      },
      arrivalAirport: {
        iataCode: 'LAX',
        name: 'Los Angeles International',
        city: 'Los Angeles',
        latitude: 33.9425,
        longitude: -118.4081,
        timezone: 'America/Los_Angeles',
        cityCode: 'LAX'
      },
      departureDate: '2025-10-05',
      departureTime: '14:00',
      arrivalTime: '18:30',
      price: 450.00
    },
    // Return flight: LAX -> YVR
    {
      flightNumber: 'WJ789',
      airline: { iataCode: 'WJ', name: 'WestJet' },
      departureAirport: {
        iataCode: 'LAX',
        name: 'Los Angeles International',
        city: 'Los Angeles',
        latitude: 33.9425,
        longitude: -118.4081,
        timezone: 'America/Los_Angeles',
        cityCode: 'LAX'
      },
      arrivalAirport: {
        iataCode: 'YVR',
        name: 'Vancouver International',
        city: 'Vancouver',
        latitude: 49.1939,
        longitude: -123.1844,
        timezone: 'America/Vancouver',
        cityCode: 'YVR'
      },
      departureDate: '2025-10-12',
      departureTime: '10:30',
      arrivalTime: '15:00',
      price: 475.00
    }
  ],
  totalPrice: 925.00, // 450 + 475
  createdAt: '2025-01-15T11:00:00Z'
}

/*
 * ================================================================
 * TEST SUITE - FlightList Component
 * ================================================================
 */

describe('FlightList Component', () => {

  /*
   * ================================================================
   * TEST 1: Loading State
   * ================================================================
   * Verifies that loading message appears when searching
   */
  it('displays loading state when searching', () => {
    render(<FlightList trips={[]} isSearching={true} />)
    
    // Check loading message is displayed
    expect(screen.getByText('Searching for flights...')).toBeInTheDocument()
    expect(screen.getByText('Please wait while we find the best options for you.')).toBeInTheDocument()
    
    // Flight cards should not be visible during loading
    expect(screen.queryByTestId('flight-card')).not.toBeInTheDocument()
  })

  /*
   * ================================================================
   * TEST 2: Empty State
   * ================================================================
   * Shows empty state when no trips are available
   */
  it('displays empty state when no trips are found', () => {
    render(<FlightList trips={[]} isSearching={false} />)
    
    // Check empty state elements
    expect(screen.getByText('Search for flights')).toBeInTheDocument()
    expect(screen.getByText('Use the search form above to find available flights.')).toBeInTheDocument()
    expect(screen.getByAltText('No flights found')).toBeInTheDocument()
    
    // No flight cards should be rendered
    expect(screen.queryByTestId('flight-card')).not.toBeInTheDocument()
  })

  /*
   * ================================================================
   * TEST 3: Flight Cards Rendering
   * ================================================================
   * Tests rendering of flight cards when trips are available
   */
  it('renders flight cards when trips are available', () => {
    const trips = [mockTrip1, mockTrip2]
    render(<FlightList trips={trips} isSearching={false} />)
    
    // Check that flight cards are rendered
    const flightCards = screen.getAllByTestId('flight-card')
    expect(flightCards).toHaveLength(2)
    
    // Verify trip data is passed to cards
    expect(screen.getByText('Flight: trip-1')).toBeInTheDocument()
    expect(screen.getByText('Flight: trip-2')).toBeInTheDocument()
    expect(screen.getByText('Price: $299.99')).toBeInTheDocument()
    expect(screen.getByText('Price: $925')).toBeInTheDocument()
  })
})
