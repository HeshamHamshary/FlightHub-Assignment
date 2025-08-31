import { useState } from 'react'
import DatePicker from 'react-datepicker'
import 'react-datepicker/dist/react-datepicker.css'
import { searchFlights, type FlightSearchParams } from '../services/flightApi'
import { type Trip } from '../types/flightTypes'

// Reusable error message component
const ErrorMessage = ({ show }: { show: boolean }) => {
  if (!show) return null
  return <span className="error-message">* Required field</span>
}

interface FlightSearchProps {
  onSearchResults: (trips: Trip[]) => void
  onSearching: (isSearching: boolean) => void
}

function FlightSearch({ onSearchResults, onSearching }: FlightSearchProps) {
  // State for trip type selection
  const [selectedTripType, setSelectedTripType] = useState('round-trip')
  
  // State for form inputs
  const [departureDate, setDepartureDate] = useState<Date | null>(null)
  const [returnDate, setReturnDate] = useState<Date | null>(null)
  const [fromCity, setFromCity] = useState('')
  const [toCity, setToCity] = useState('')
  
  // State for validation errors
  const [errors, setErrors] = useState({
    fromCity: false,
    toCity: false,
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
    }
  }

  // Handle swap button click
  const handleSwap = () => {
    const tempCity = fromCity
    setFromCity(toCity)
    setToCity(tempCity)
    
    // Add active state briefly
    setIsSwapActive(true)
    setTimeout(() => setIsSwapActive(false), 150)
  }

  // Handle search button click
  const handleSearch = async () => {
    // Reset errors
    setErrors({
      fromCity: false,
      toCity: false,
      departureDate: false,
      returnDate: false
    })
    
    // Validate required fields
    const newErrors = {
      fromCity: !fromCity.trim(),
      toCity: !toCity.trim(),
      departureDate: !departureDate,
      returnDate: selectedTripType === 'round-trip' && !returnDate
    }
    
    setErrors(newErrors)
    
    // Check if there are any errors
    const hasErrors = Object.values(newErrors).some(error => error)
    
    if (hasErrors) {
      return
    }
    
    // Prepare search parameters
    const searchParams: FlightSearchParams = {
      tripType: selectedTripType as 'one-way' | 'round-trip',
      fromAirport: fromCity,
      toAirport: toCity,
      departureDate: departureDate?.toISOString().split('T')[0], // YYYY-MM-DD format
      returnDate: returnDate?.toISOString().split('T')[0], // YYYY-MM-DD format
      passengers: 1
    }
    
    try {
      onSearching(true)
      const trips = await searchFlights(searchParams)
      onSearchResults(trips)
    } catch (error) {
      console.error('Search failed:', error)
      // You could add error handling here (show toast, etc.)
    } finally {
      onSearching(false)
    }
  }

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
            <div className={`input-container ${errors.fromCity ? 'error' : ''}`}>
              <span className="icon">✈️</span>
              <input 
                type="text" 
                placeholder="Enter departure city" 
                value={fromCity}
                onChange={(e) => setFromCity(e.target.value)}
              />
              <span className="clear-icon" onClick={() => setFromCity('')}>×</span>
            </div>
            <ErrorMessage show={errors.fromCity} />
          </div>

          {/* Swap button */}
          <div className={`swap-button ${isSwapActive ? 'active' : ''}`} onClick={handleSwap}>🔄</div>

          {/* To field */}
          <div className="form-field">
            <label>Going to</label>
            <div className={`input-container ${errors.toCity ? 'error' : ''}`}>
              <span className="icon">✈️</span>
              <input 
                type="text" 
                placeholder="Enter destination city" 
                value={toCity}
                onChange={(e) => setToCity(e.target.value)}
              />
              <span className="clear-icon" onClick={() => setToCity('')}>×</span>
            </div>
            <ErrorMessage show={errors.toCity} />
          </div>

          {/* Departure date */}
          <div className="form-field">
            <label>Departing</label>
            <div className={`input-container ${errors.departureDate ? 'error' : ''}`}>
              <span className="icon">📅</span>
              <DatePicker
                selected={departureDate}
                onChange={(date) => setDepartureDate(date)}
                placeholderText="Select date"
                dateFormat="EEE, MMM dd, yyyy"
                minDate={new Date()}
                maxDate={returnDate || undefined}
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
                  onChange={(date) => setReturnDate(date)}
                  placeholderText="Select date"
                  dateFormat="EEE, MMM dd, yyyy"
                  minDate={departureDate || new Date()}
                  className="date-input"
                  onKeyDown={(e) => e.preventDefault()}
                />
              </div>
              <ErrorMessage show={errors.returnDate} />
            </div>
          )}

          {/* Search button */}
          <div className="search-button-container">
            <button className="search-button" onClick={handleSearch}>
              🔍 Search
            </button>
          </div>
        </div>

      </div>
    </div>
  )
}

export default FlightSearch
