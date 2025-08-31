import { useState } from 'react'
import DatePicker from 'react-datepicker'
import 'react-datepicker/dist/react-datepicker.css'

function FlightSearch() {
  // State for trip type selection
  const [selectedTripType, setSelectedTripType] = useState('round-trip')
  
  // State for dates
  const [departureDate, setDepartureDate] = useState<Date | null>(null)
  const [returnDate, setReturnDate] = useState<Date | null>(null)

  // Handle trip type selection
  const handleTripTypeClick = (tripType: string) => {
    setSelectedTripType(tripType)
    // Clear return date when switching to one-way
    if (tripType === 'one-way') {
      setReturnDate(null)
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
            <div className="input-container">
              <span className="icon">✈️</span>
              <input type="text" placeholder="Enter departure city" />
              <span className="clear-icon">×</span>
            </div>
          </div>

          {/* Swap button */}
          <div className="swap-button">🔄</div>

          {/* To field */}
          <div className="form-field">
            <label>Going to</label>
            <div className="input-container">
              <span className="icon">✈️</span>
              <input type="text" placeholder="Enter destination city" />
              <span className="clear-icon">×</span>
            </div>
          </div>

          {/* Departure date */}
          <div className="form-field">
            <label>Departing</label>
            <div className="input-container">
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
          </div>

          {/* Return date - only show for round trip */}
          {selectedTripType === 'round-trip' && (
            <div className="form-field">
              <label>Returning</label>
              <div className="input-container">
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
            </div>
          )}

          {/* Search button */}
          <div className="search-button-container">
            <button className="search-button">
              🔍 Search
            </button>
          </div>
        </div>

      </div>
    </div>
  )
}

export default FlightSearch
