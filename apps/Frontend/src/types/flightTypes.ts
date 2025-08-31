/**
 * Airline entity
 */
export interface Airline {
  /** IATA Airline Code */
  iataCode: string
  /** Full airline name */
  name: string
}

/**
 * Airport entity
 */
export interface Airport {
  /** IATA Airport Code */
  iataCode: string
  /** Full airport name */
  name: string
  /** City name */
  city: string
  /** Latitude coordinate */
  latitude: number
  /** Longitude coordinate */
  longitude: number
  /** Timezone identifier */
  timezone: string
  /** IATA City Code */
  cityCode: string
}

/**
 * Flight entity
 */
export interface Flight {
  /** Unique flight number for the airline */
  flightNumber: string
  /** Reference to the airline operating this flight */
  airline: Airline
  /** Reference to the departure airport */
  departureAirport: Airport
  /** Reference to the arrival airport */
  arrivalAirport: Airport
  /** Departure date */
  departureDate: string
  /** Departure time in the departure airport's timezone */
  departureTime: string
  /** Arrival time in the arrival airport's timezone */
  arrivalTime: string
  /** Price for a single passenger in neutral currency */
  price: number
}

/**
 * Trip entity
 */
export interface Trip {
  /** Unique trip identifier */
  id: string
  /** Type of trip */
  type: 'one-way' | 'round-trip'
  /** Array of flights */
  flights: Flight[]
  /** Total price of all flights */
  totalPrice: number
  /** Trip creation timestamp */
  createdAt: string
}
