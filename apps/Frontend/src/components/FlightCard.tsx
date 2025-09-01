import { memo } from 'react'
import { type Flight, type Trip } from '../types/flightTypes'

// Flight component interface
export interface FlightProps {
  trip: Trip
}

// Reusable Flight Route component
const FlightRoute = ({ flight }: { flight: Flight }) => (
  <div className="flight-route">
    <div className="airline-info">
      <span className="airline-name">{flight.airline.name}</span>
    </div>
    
    <div className="route-info">
      <div className="departure">
        <div className="time">{flight.departureTime}</div>
        <div className="airport">{flight.departureAirport.iataCode}</div>
      </div>
      
      <div className="flight-path">
        <div className="departure-date">{flight.departureDate}</div>
        <div className="duration">{flight.duration}</div>
      </div>
      
      <div className="arrival">
        <div className="time">{flight.arrivalTime}</div>
        <div className="airport">{flight.arrivalAirport.iataCode}</div>
      </div>
    </div>
  </div>
)

const FlightCard = memo(({ trip }: FlightProps) => {
  return (
    <div className="flight-card">
      <div className="flight-content">
        {/* Flight Details */}
        <div className="flight-details">
          {trip.type === 'one-way' ? (
            <FlightRoute flight={trip.flights[0]} />
          ) : (
            <div className="round-trip-flights">
              {trip.flights.map((flight, index) => (
                <FlightRoute 
                  key={index}
                  flight={flight} 
                />
              ))}
            </div>
          )}
        </div>

        {/* Price and Actions */}
        <div className="flight-actions">
          <div className="price-info">
            <div className="price">CAD {trip.totalPrice}</div>
            <div className="taxes">taxes included</div>
          </div>
          
          <div className="action-buttons">
            <button className="btn-primary">Select</button>
          </div>
        </div>
      </div>
    </div>
  )
})

export default FlightCard
