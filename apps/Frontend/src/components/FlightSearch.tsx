import { useState, useEffect } from 'react'
import DatePicker from 'react-datepicker'
import 'react-datepicker/dist/react-datepicker.css'
import { searchFlights, getAvailableAirlines, type FlightSearchParams, type PaginationMeta } from '../services/flightApi'
import { type Trip } from '../types/flightTypes'
import { MAJOR_AIRPORTS } from '../utils/constants'

// Reusable error message component
const ErrorMessage = ({ show }: { show: boolean }) => {
  if (!show) return null
  return <span className="error-message">* Required field</span>
}

interface FlightSearchProps {
  onSearchResults: (trips: Trip[], meta: PaginationMeta, searchParams: FlightSearchParams) => void
  onSearching: (isSearching: boolean) => void
  searchParams: FlightSearchParams | null
}

function FlightSearch({ onSearchResults, onSearching, searchParams }: FlightSearchProps) {
  // State for trip type selection
  const [selectedTripType, setSelectedTripType] = useState('one-way')
  
  // State for form inputs - now using airport codes
  const [departureDate, setDepartureDate] = useState<Date | null>(null)
  const [returnDate, setReturnDate] = useState<Date | null>(null)
  const [fromAirport, setFromAirport] = useState('')
  const [toAirport, setToAirport] = useState('')
  const [preferredAirline, setPreferredAirline] = useState('')
  
  // State for airlines data
  const [airlines, setAirlines] = useState<Array<{iataCode: string, name: string}>>([])
  const [loadingAirlines, setLoadingAirlines] = useState(false)
  
  // State for validation errors
  const [errors, setErrors] = useState({
    fromAirport: false,
    toAirport: false,
    departureDate: false,
    returnDate: false
  })
  
  // State for button interactions
  const [isSwapActive, setIsSwapActive] = useState(false)

  // Handle trip type selection
  const handleTripTypeClick = (tripType: string) => {
    setSelectedTripType(tripType)
    // Clear return date when switching to one-way
    if (tripType === 'one-way') {
      setReturnDate(null)
    } else {
      // Clear preferred airline when switching to round-trip
      setPreferredAirline('')
    }
  }

  // Handle swap button click
  const handleSwap = () => {
    const tempAirport = fromAirport
    setFromAirport(toAirport)
    setToAirport(tempAirport)
    
    // Add active state briefly
    setIsSwapActive(true)
    setTimeout(() => setIsSwapActive(false), 150)
  }

  // Handle search - can be called manually or automatically
  const performSearch = async (customParams?: FlightSearchParams) => {
    const params = customParams || {
      tripType: selectedTripType as 'one-way' | 'round-trip',
      fromAirport: fromAirport,
      toAirport: toAirport,
      departureDate: departureDate?.toISOString().split('T')[0], // YYYY-MM-DD format
      returnDate: returnDate?.toISOString().split('T')[0], // YYYY-MM-DD format
      preferredAirline: preferredAirline || undefined, // Only include if selected
      passengers: 1
    }
    
    try {
      onSearching(true)
      const { trips, meta } = await searchFlights(params)
      onSearchResults(trips, meta, params)
    } catch (error) {
      console.error('Search failed:', error)
    } finally {
      onSearching(false)
    }
  }

  // Handle search button click
  const handleSearch = async () => {
    // Reset errors
    setErrors({
      fromAirport: false,
      toAirport: false,
      departureDate: false,
      returnDate: false
    })
    
    // Validate required fields
    const newErrors = {
      fromAirport: !fromAirport,
      toAirport: !toAirport,
      departureDate: !departureDate,
      returnDate: selectedTripType === 'round-trip' && !returnDate
    }
    
    setErrors(newErrors)
    
    // Check if there are any errors
    const hasErrors = Object.values(newErrors).some(error => error)
    
    if (hasErrors) {
      return
    }
    
    await performSearch()
  }

  // Check if search criteria is sufficient to load airlines
  const hasSearchCriteria = () => {
    const hasBasicCriteria = fromAirport && toAirport && departureDate
    // For round-trip, we don't need return date to load airlines since preferred airline is only for one-way
    return hasBasicCriteria
  }

  // Load available airlines when search criteria changes
  useEffect(() => {
    const loadAvailableAirlines = async () => {
      if (!hasSearchCriteria()) {
        setAirlines([])
        setPreferredAirline('') // Clear selection when criteria incomplete
        return
      }

      setLoadingAirlines(true)
      try {
        const searchCriteria = {
          tripType: selectedTripType as 'one-way' | 'round-trip',
          fromAirport,
          toAirport,
          departureDate: departureDate?.toISOString().split('T')[0],
          returnDate: returnDate?.toISOString().split('T')[0],
        }
        
        const airlineData = await getAvailableAirlines(searchCriteria)
        setAirlines(airlineData)
        
        // Clear preferred airline if it's no longer available
        if (preferredAirline && !airlineData.find(a => a.iataCode === preferredAirline)) {
          setPreferredAirline('')
        }
      } catch (error) {
        console.error('Failed to load available airlines:', error)
        setAirlines([])
      } finally {
        setLoadingAirlines(false)
      }
    }
    
    loadAvailableAirlines()
  }, [selectedTripType, fromAirport, toAirport, departureDate, returnDate])

  // Handle automatic search when searchParams change (for pagination)
  useEffect(() => {
    if (searchParams && searchParams.page) {
      performSearch(searchParams)
    }
  }, [searchParams])

  return (
    <div className="flight-search-container">
      {/* Main search card */}
      <div className="search-card">
        
        {/* Trip type selection */}
        <div className="trip-type-section">
          <div 
            className={`trip-type ${selectedTripType === 'round-trip' ? 'active' : ''}`}
            onClick={() => handleTripTypeClick('round-trip')}
          >
            Round trip
          </div>
          <div 
            className={`trip-type ${selectedTripType === 'one-way' ? 'active' : ''}`}
            onClick={() => handleTripTypeClick('one-way')}
          >
            One way
          </div>
        </div>

        {/* Flight search form */}
        <div className={`search-form ${selectedTripType === 'one-way' ? 'one-way' : ''}`}>
          {/* From field */}
          <div className="form-field">
            <label>Leaving from</label>
            <div className={`input-container ${errors.fromAirport ? 'error' : ''}`}>
              <span className="icon">✈️</span>
              <select 
                value={fromAirport}
                onChange={(e) => setFromAirport(e.target.value)}
                className="city-select"
              >
                <option value="">Departure airport</option>
                {Object.entries(MAJOR_AIRPORTS)
                  .filter(([code]) => code !== toAirport) // Filter out the destination airport
                  .map(([code, city]) => (
                    <option key={code} value={code}>
                      {code} {city}
                    </option>
                  ))}
              </select>
            </div>
            <ErrorMessage show={errors.fromAirport} />
          </div>

          {/* Swap button */}
          <div className={`swap-button ${isSwapActive ? 'active' : ''}`} onClick={handleSwap}>🔄</div>

          {/* To field */}
          <div className="form-field">
            <label>Going to</label>
            <div className={`input-container ${errors.toAirport ? 'error' : ''}`}>
              <span className="icon">✈️</span>
              <select 
                value={toAirport}
                onChange={(e) => setToAirport(e.target.value)}
                className="city-select"
              >
                <option value="">Destination airport</option>
                {Object.entries(MAJOR_AIRPORTS)
                  .filter(([code]) => code !== fromAirport) // Filter out the departure airport
                  .map(([code, city]) => (
                    <option key={code} value={code}>
                      {code} {city}
                    </option>
                  ))}
              </select>
            </div>
            <ErrorMessage show={errors.toAirport} />
          </div>

          {/* Departure date */}
          <div className="form-field">
            <label>Departing</label>
            <div className={`input-container ${errors.departureDate ? 'error' : ''}`}>
              <span className="icon">📅</span>
              <DatePicker
                selected={departureDate}
                onChange={(date) => setDepartureDate(Array.isArray(date) ? date[0] : date)}
                placeholderText="Select date"
                dateFormat="EEE, MMM dd, yyyy"
                minDate={new Date()}
                maxDate={returnDate || new Date(2026, 0, 1)}
                className="date-input"
                onKeyDown={(e) => e.preventDefault()}
              />
            </div>
            <ErrorMessage show={errors.departureDate} />
          </div>

          {/* Return date - only show for round trip */}
          {selectedTripType === 'round-trip' && (
            <div className="form-field">
              <label>Returning</label>
              <div className={`input-container ${errors.returnDate ? 'error' : ''}`}>
                <span className="icon">📅</span>
                <DatePicker
                  selected={returnDate}
                  onChange={(date) => setReturnDate(Array.isArray(date) ? date[0] : date)}
                  placeholderText="Select date"
                  dateFormat="EEE, MMM dd, yyyy"
                  minDate={departureDate || new Date()}
                  maxDate={new Date(2026, 0, 1)}
                  className="date-input"
                  onKeyDown={(e) => e.preventDefault()}
                />
              </div>
              <ErrorMessage show={errors.returnDate} />
            </div>
          )}

          {/* Preferred Airline - only show for one-way trips */}
          {selectedTripType === 'one-way' && (
            <div className="form-field">
              <label>Preferred airline <span className="optional">(optional)</span></label>
              <div className={`input-container ${!hasSearchCriteria() ? 'disabled' : ''}`}>
                <span className="icon">✈️</span>
                <select 
                  value={preferredAirline}
                  onChange={(e) => setPreferredAirline(e.target.value)}
                  className="airline-select"
                  disabled={!hasSearchCriteria() || loadingAirlines}
                >
                  {!hasSearchCriteria() ? (
                    <option value="">Fill search criteria first</option>
                  ) : loadingAirlines ? (
                    <option value="">Loading airlines...</option>
                  ) : airlines.length === 0 ? (
                    <option value="">No airlines available</option>
                  ) : (
                    <>
                      <option value="">Any airline</option>
                      {airlines.map((airline) => (
                        <option key={airline.iataCode} value={airline.iataCode}>
                          {airline.iataCode} - {airline.name}
                        </option>
                      ))}
                    </>
                  )}
                </select>
              </div>
            </div>
          )}

          {/* Search button */}
          <div className="search-button-container">
            <button className="search-button" onClick={handleSearch}>
              Search
            </button>
          </div>
        </div>

      </div>
    </div>
  )
}

export default FlightSearch
