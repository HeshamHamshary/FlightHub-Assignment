

function FlightSearch() {
  return (
    <div className="flight-search-container">
      {/* Main search card */}
      <div className="search-card">
        
        {/* Trip type selection */}
        <div className="trip-type-section">
          <div className="trip-type active">Round trip</div>
          <div className="trip-type">One way</div>
          <div className="trip-type">Multi-city</div>
        </div>

        {/* Flight search form */}
        <div className="search-form">
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

          {/* Return date */}
          <div className="form-field">
            <label>Returning</label>
            <div className="input-container">
              <span className="icon">📅</span>
              <input type="date" />
            </div>
          </div>

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
