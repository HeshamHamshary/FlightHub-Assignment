import { useState } from 'react'

function FlightSearch() {
  // State for trip type selection
  const [selectedTripType, setSelectedTripType] = useState('round-trip')

  // Handle trip type selection
  const handleTripTypeClick = (tripType: string) => {
    setSelectedTripType(tripType)
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
              <input type="date" />
            </div>
          </div>

          {/* Return date - only show for round trip */}
          {selectedTripType === 'round-trip' && (
            <div className="form-field">
              <label>Returning</label>
              <div className="input-container">
                <span className="icon">📅</span>
                <input type="date" />
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
