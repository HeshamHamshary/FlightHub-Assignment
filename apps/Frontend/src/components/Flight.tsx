// Flight type constants
export const FlightType = {
  ONE_WAY: 'one-way',
  ROUND_TRIP: 'round-trip'
} as const

export type FlightType = typeof FlightType[keyof typeof FlightType]

// Flight component interface
export interface FlightProps {
  type: FlightType
  outboundFlight: {
    airline: string
    departure: string
    arrival: string
    departureTime: string
    arrivalTime: string
    duration: string
    price: number
  }
  returnFlight?: {
    airline: string
    departure: string
    arrival: string
    departureTime: string
    arrivalTime: string
    duration: string
    price: number
  }
  totalPrice?: number
}

// Reusable Flight Route component
const FlightRoute = ({ flight }: { flight: FlightProps['outboundFlight'] | FlightProps['returnFlight'] }) => (
  <div className="flight-route">
    <div className="airline-info">
      <span className="airline-name">{flight?.airline}</span>
    </div>
    
    <div className="route-info">
      <div className="departure">
        <div className="time">{flight?.departureTime}</div>
        <div className="airport">{flight?.departure}</div>
      </div>
      
      <div className="flight-path">
        <div className="duration">{flight?.duration}</div>
      </div>
      
      <div className="arrival">
        <div className="time">{flight?.arrivalTime}</div>
        <div className="airport">{flight?.arrival}</div>
      </div>
    </div>
  </div>
)

const Flight = ({ type, outboundFlight, returnFlight, totalPrice }: FlightProps) => {
  return (
    <div className="flight-card">
      <div className="flight-content">
        {/* Flight Details */}
        <div className="flight-details">
          {type === FlightType.ONE_WAY ? (
            <FlightRoute flight={outboundFlight} />
          ) : (
            <div className="round-trip-flights">
              <FlightRoute flight={outboundFlight} />
              {returnFlight && <FlightRoute flight={returnFlight} />}
            </div>
          )}
        </div>

        {/* Price and Actions */}
        <div className="flight-actions">
          <div className="price-info">
            <div className="price">CAD {type === FlightType.ONE_WAY ? outboundFlight.price : totalPrice}</div>
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

export default Flight
