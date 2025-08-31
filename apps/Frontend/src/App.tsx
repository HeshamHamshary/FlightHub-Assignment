import { useState } from 'react'
import Header from './components/Header'
import FlightSearch from './components/FlightSearch'
import FlightList from './components/FlightList'
import { type Trip } from './types/flightTypes'

function App() {
  const [trips, setTrips] = useState<Trip[]>([])
  const [isSearching, setIsSearching] = useState(false)

  const handleSearchResults = (searchResults: Trip[]) => {
    setTrips(searchResults)
  }

  const handleSearching = (searching: boolean) => {
    setIsSearching(searching)
  }

  return (
    <div className="app-container">

      <Header />
      
      <FlightSearch 
        onSearchResults={handleSearchResults}
        onSearching={handleSearching}
      />
      
      <FlightList 
        trips={trips}
        isSearching={isSearching}
      />
      
    </div>
  )
}

export default App
