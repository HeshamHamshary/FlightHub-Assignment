import { type Trip } from '../types/flightTypes'
import { API_ENDPOINTS } from '../utils/config'

export interface FlightSearchParams {
  fromAirport?: string
  toAirport?: string
  departureDate?: string
  returnDate?: string
  tripType?: 'one-way' | 'round-trip'
  passengers?: number
}

export interface FlightSearchResponse {
  status: string
  query: FlightSearchParams
  flights: Trip[]
}

export const searchFlights = async (params: FlightSearchParams = {}): Promise<Trip[]> => {
  try {
    // Build query string from params
    const queryParams = new URLSearchParams()
    Object.entries(params).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        queryParams.append(key, value.toString())
      }
    })

    const url = queryParams.toString() 
      ? `${API_ENDPOINTS.FLIGHT_SEARCH}?${queryParams.toString()}`
      : API_ENDPOINTS.FLIGHT_SEARCH

    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    })

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    const data: FlightSearchResponse = await response.json()
    
    // Parse the response into our Trip types
    return data.flights.map((tripData: any) => ({
      id: tripData.id,
      type: tripData.type,
      flights: tripData.flights.map((flightSegment: any) => ({
        flight: {
          flightNumber: flightSegment.flight.flightNumber,
          airline: {
            iataCode: flightSegment.flight.airline.iataCode,
            name: flightSegment.flight.airline.name,
          },
          departureAirport: {
            iataCode: flightSegment.flight.departureAirport.iataCode,
            name: flightSegment.flight.departureAirport.name,
            city: flightSegment.flight.departureAirport.city,
            latitude: flightSegment.flight.departureAirport.latitude,
            longitude: flightSegment.flight.departureAirport.longitude,
            timezone: flightSegment.flight.departureAirport.timezone,
            cityCode: flightSegment.flight.departureAirport.cityCode,
          },
          arrivalAirport: {
            iataCode: flightSegment.flight.arrivalAirport.iataCode,
            name: flightSegment.flight.arrivalAirport.name,
            city: flightSegment.flight.arrivalAirport.city,
            latitude: flightSegment.flight.arrivalAirport.latitude,
            longitude: flightSegment.flight.arrivalAirport.longitude,
            timezone: flightSegment.flight.arrivalAirport.timezone,
            cityCode: flightSegment.flight.arrivalAirport.cityCode,
          },
          departureTime: flightSegment.flight.departureTime,
          arrivalTime: flightSegment.flight.arrivalTime,
          price: flightSegment.flight.price,
        },
        departureDate: flightSegment.departureDate,
      })),
      totalPrice: tripData.totalPrice,
      createdAt: tripData.createdAt,
    }))
  } catch (error) {
    console.error('Error fetching flights:', error)
    throw error
  }
}
