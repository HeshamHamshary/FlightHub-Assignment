import FlightCard from './FlightCard'
import { type Trip} from '../types/flightTypes'
import noFlightsImage from '../assets/airplane.svg'

// Main FlightList component
function FlightList() {
  // Mock trips
  const trips: Trip[] = []

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
        {sortedTrips.length === 0 ? (
          <div className="no-flights-message">
            <img className="logos" src={noFlightsImage} alt="No flights found" />
            <h3>Flights will show up here!</h3>
            <p>Search for flights to see available options.</p>
          </div>
        ) : (
          sortedTrips.map((trip) => (
            <FlightCard
              key={trip.id}
              trip={trip}
            />
          ))
        )}
      </div>
    </div>
  )
}

export default FlightList
