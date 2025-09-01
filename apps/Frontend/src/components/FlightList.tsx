import { useMemo } from 'react'
import FlightCard from './FlightCard'
import { type Trip } from '../types/flightTypes'
import noFlightsImage from '../../public/airplane.svg'

interface FlightListProps {
  trips: Trip[]
  isSearching: boolean
}

// Main FlightList component
function FlightList({ trips, isSearching }: FlightListProps) {

  // Memoize the empty state check
  const hasNoTrips = useMemo(() => trips.length === 0, [trips.length])

  return (
    <div className="flight-list-container">
      {/* Header */}
      <div className="flight-list-header">
        <h2>Flight Results</h2>
      </div>

      {/* Loading state */}
      {isSearching && (
        <div className="loading-message">
          <h3>Searching for flights...</h3>
          <p>Please wait while we find the best options for you.</p>
        </div>
      )}

      {/* Flight cards */}
      {!isSearching && (
        <div className="flight-cards">
          {hasNoTrips ? (
            <div className="no-flights-message">
              <img className="logos" src={noFlightsImage} alt="No flights found" />
              <h3>Search for flights</h3>
              <p>Use the search form above to find available flights.</p>
            </div>
          ) : (
            trips.map((trip) => (
              <FlightCard
                key={trip.id}
                trip={trip}
              />
            ))
          )}
        </div>
      )}
    </div>
  )
}

export default FlightList
