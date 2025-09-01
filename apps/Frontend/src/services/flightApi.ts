import { type Trip } from '../types/flightTypes'
import { API_ENDPOINTS } from '../utils/config'

export interface FlightSearchParams {
  fromAirport?: string
  toAirport?: string
  departureDate?: string
  returnDate?: string
  tripType?: 'one-way' | 'round-trip'
  passengers?: number
  page?: number
}

export interface PaginationMeta {
  total: number
  page: number
  perPage: number
  totalPages: number
  hasNextPage: boolean
  hasPreviousPage: boolean
  executionTimeMs: number
}

export interface FlightSearchResponse {
  status: string
  query: FlightSearchParams
  flights: Trip[]
  meta: PaginationMeta
}

export const searchFlights = async (params: FlightSearchParams = {}): Promise<{trips: Trip[], meta: PaginationMeta}> => {
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
    const trips = data.flights.map((tripData: any) => ({
      id: tripData.id,
      type: tripData.type,
      flights: tripData.flights.map((flight: any) => ({
        flightNumber: flight.flightNumber,
        airline: {
          iataCode: flight.airline.iataCode,
          name: flight.airline.name,
        },
        departureAirport: {
          iataCode: flight.departureAirport.iataCode,
          name: flight.departureAirport.name,
          city: flight.departureAirport.city,
          latitude: flight.departureAirport.latitude,
          longitude: flight.departureAirport.longitude,
          timezone: flight.departureAirport.timezone,
          cityCode: flight.departureAirport.cityCode,
        },
        arrivalAirport: {
          iataCode: flight.arrivalAirport.iataCode,
          name: flight.arrivalAirport.name,
          city: flight.arrivalAirport.city,
          latitude: flight.arrivalAirport.latitude,
          longitude: flight.arrivalAirport.longitude,
          timezone: flight.arrivalAirport.timezone,
          cityCode: flight.arrivalAirport.cityCode,
        },
        departureTime: flight.departureTime,
        arrivalTime: flight.arrivalTime,
        price: flight.price,
        departureDate: flight.departureDate,
      })),
      totalPrice: tripData.totalPrice,
      createdAt: tripData.createdAt,
    }))

    return { trips, meta: data.meta }
  } catch (error) {
    console.error('Error fetching flights:', error)
    throw error
  }
}
