import Flight, { FlightType } from './Flight'

// Main FlightList component
function FlightList() {

  // Mock flight data
  const flights = [
    {
      id: 1,
      type: FlightType.ONE_WAY,
      outboundFlight: {
        airline: 'Air Canada',
        departure: 'YUL',
        arrival: 'PEK',
        departureTime: '7:30 AM',
        arrivalTime: '4:10 PM +1',
        layover: '2h 30m in YVR',
        duration: '20h 40m',
        price: 841.39
      }
    },
    {
      id: 2,
      type: FlightType.ROUND_TRIP,
      outboundFlight: {
        airline: 'Air Canada',
        departure: 'YUL',
        arrival: 'PEK',
        departureTime: '7:30 AM',
        arrivalTime: '4:10 PM +1',
        layover: '2h 30m in YVR',
        duration: '20h 40m',
        price: 841.39
      },
      returnFlight: {
        airline: 'Air Canada',
        departure: 'PEK',
        arrival: 'YUL',
        departureTime: '6:05 PM',
        arrivalTime: '12:36 AM +1',
        layover: '2h 30m in YVR',
        duration: '18h 31m',
        price: 512.30
      }
    }
  ]

  // Sort flights by total price (lowest first)
  const sortedFlights = [...flights].sort((a, b) => {
    const aTotalPrice = a.type === FlightType.ONE_WAY 
      ? a.outboundFlight.price 
      : a.outboundFlight.price + (a.returnFlight?.price || 0)
    const bTotalPrice = b.type === FlightType.ONE_WAY 
      ? b.outboundFlight.price 
      : b.outboundFlight.price + (b.returnFlight?.price || 0)
    return aTotalPrice - bTotalPrice
  })

  return (
    <div className="flight-list-container">
      {/* Header */}
      <div className="flight-list-header">
        <h2>Flight Results</h2>
      </div>

      {/* Flight cards */}
      <div className="flight-cards">
        {sortedFlights.map((flight) => (
          <Flight
            key={flight.id}
            type={flight.type}
            outboundFlight={flight.outboundFlight}
            returnFlight={flight.returnFlight}
            totalPrice={flight.type === FlightType.ONE_WAY 
              ? flight.outboundFlight.price 
              : flight.outboundFlight.price + (flight.returnFlight?.price || 0)
            }
          />
        ))}
      </div>
    </div>
  )
}

export default FlightList
