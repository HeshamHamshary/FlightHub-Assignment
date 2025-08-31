import { type Flight, type Trip } from '../types/flightTypes'

// Flight component interface
export interface FlightProps {
  trip: Trip
}

// Reusable Flight Route component
const FlightRoute = ({ flight, departureDate }: { flight: Flight; departureDate: string }) => (
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
        <div className="duration">20h 40m</div>
      </div>
      
      <div className="arrival">
        <div className="time">{flight.arrivalTime}</div>
        <div className="airport">{flight.arrivalAirport.iataCode}</div>
      </div>
    </div>
  </div>
)

const FlightCard = ({ trip }: FlightProps) => {
  return (
    <div className="flight-card">
      <div className="flight-content">
        {/* Flight Details */}
        <div className="flight-details">
          {trip.type === 'one-way' ? (
            <FlightRoute flight={trip.flights[0].flight} departureDate={trip.flights[0].departureDate} />
          ) : (
            <div className="round-trip-flights">
              {trip.flights.map((flightSegment, index) => (
                <FlightRoute 
                  key={index}
                  flight={flightSegment.flight} 
                  departureDate={flightSegment.departureDate} 
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
}

export default FlightCard
