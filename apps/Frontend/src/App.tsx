import { useState } from 'react'
import Header from './components/Header'
import FlightSearch from './components/FlightSearch'
import FlightList from './components/FlightList'
import Pagination from './components/Pagination'
import { type Trip } from './types/flightTypes'
import { type PaginationMeta, type FlightSearchParams } from './services/flightApi'

function App() {
  const [trips, setTrips] = useState<Trip[]>([])
  const [isSearching, setIsSearching] = useState(false)
  const [paginationMeta, setPaginationMeta] = useState<PaginationMeta | null>(null)
  const [lastSearchParams, setLastSearchParams] = useState<FlightSearchParams | null>(null)

  const handleSearchResults = (searchResults: Trip[], meta: PaginationMeta, searchParams: FlightSearchParams) => {
    setTrips(searchResults)
    setPaginationMeta(meta)
    setLastSearchParams(searchParams)
  }

  const handleSearching = (searching: boolean) => {
    setIsSearching(searching)
  }

  const handlePageChange = (page: number) => {
    if (lastSearchParams) {
      // Re-run search with new page number
      const newSearchParams = { ...lastSearchParams, page }
      // The FlightSearch component will handle the actual search
      setLastSearchParams(newSearchParams)
    }
  }

  return (
    <div className="app-container">

      <Header />
      
      <FlightSearch 
        onSearchResults={handleSearchResults}
        onSearching={handleSearching}
        searchParams={lastSearchParams}
      />
      
      <FlightList 
        trips={trips}
        isSearching={isSearching}
      />

      <Pagination 
        meta={paginationMeta}
        onPageChange={handlePageChange}
      />
      
    </div>
  )
}

export default App
