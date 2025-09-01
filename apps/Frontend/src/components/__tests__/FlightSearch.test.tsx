import { render, screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import FlightSearch from '../FlightSearch'

/*
 * ================================================================
 * MOCKS - External Dependencies
 * ================================================================
 */

// Mock the flight API so we don't make real HTTP requests during tests
jest.mock('../../services/flightApi', () => ({
  searchFlights: jest.fn(() => Promise.resolve({ trips: [], meta: {} }))
}))

// Mock the react-datepicker library with a simple inp  ut
jest.mock('react-datepicker', () => {
  return function MockDatePicker({ selected, onChange, placeholderText }: any) {
    return (
      <input
        data-testid="date-picker"
        type="text"
        value={selected ? selected.toISOString().split('T')[0] : ''}
        onChange={(e) => onChange && onChange(new Date(e.target.value))}
        placeholder={placeholderText}
      />
    )
  }
})

// Mock CSS imports to prevent errors
jest.mock('react-datepicker/dist/react-datepicker.css', () => ({}))

/*
 * ================================================================
 * TEST SUITE - FlightSearch Component
 * ================================================================
 */

describe('FlightSearch Component', () => {
  // Mock functions for component props
  const mockOnSearchResults = jest.fn()
  const mockOnSearching = jest.fn()

  // Reset all mocks before each test to ensure clean state
  beforeEach(() => {
    jest.clearAllMocks()
  })

  // Default props used in all tests
  const defaultProps = {
    onSearchResults: mockOnSearchResults,
    onSearching: mockOnSearching,
    searchParams: null
  }

  /*
   * ================================================================
   * TEST 1: Basic Rendering
   * ================================================================
   * Verifies that all essential form elements are present
   */
  it('renders all form elements correctly', () => {
    render(<FlightSearch {...defaultProps} />)
    
    // Check that all major UI elements exist
    expect(screen.getByText('Round trip')).toBeInTheDocument()
    expect(screen.getByText('One way')).toBeInTheDocument()
    expect(screen.getByText('Leaving from')).toBeInTheDocument()
    expect(screen.getByText('Going to')).toBeInTheDocument()
    expect(screen.getByText('Departing')).toBeInTheDocument()
    expect(screen.getByRole('button', { name: /search/i })).toBeInTheDocument()
  })

  /*
   * ================================================================
   * TEST 2: Default State
   * ================================================================
   * Ensures round-trip is selected by default
   */
  it('shows round trip as default selection', () => {
    render(<FlightSearch {...defaultProps} />)
    
    const roundTripButton = screen.getByText('Round trip')
    const oneWayButton = screen.getByText('One way')
    
    expect(roundTripButton).toHaveClass('active')
    expect(oneWayButton).not.toHaveClass('active')
    expect(screen.getByText('Returning')).toBeInTheDocument()
  })

  /*
   * ================================================================
   * TEST 3: Trip Type Switching
   * ================================================================
   * Tests switching between round-trip and one-way modes
   */
  it('switches between trip types correctly', async () => {
    const user = userEvent.setup()
    render(<FlightSearch {...defaultProps} />)
    
    const oneWayButton = screen.getByText('One way')
    
    // Switch to one-way trip
    await user.click(oneWayButton)
    
    // Verify one-way is now active and return field is hidden
    expect(oneWayButton).toHaveClass('active')
    expect(screen.queryByText('Returning')).not.toBeInTheDocument()
  })

  /*
   * ================================================================
   * TEST 4: Form Validation
   * ================================================================
   * Tests that validation errors appear for empty required fields
   */
  it('shows validation errors for empty fields', async () => {
    const user = userEvent.setup()
    render(<FlightSearch {...defaultProps} />)
    
    const searchButton = screen.getByRole('button', { name: /search/i })
    
    // Try to search without filling any fields
    await user.click(searchButton)
    
    // Should show 4 error messages (from, to, departure, return)
    await waitFor(() => {
      expect(screen.getAllByText('* Required field')).toHaveLength(4)
    })
  })

  /*
   * ================================================================
   * TEST 5: Airport Selectors
   * ================================================================
   * Verifies that airport dropdown selectors are present
   */
  it('displays airport selection dropdowns', () => {
    render(<FlightSearch {...defaultProps} />)
    
    expect(screen.getByDisplayValue('Departure airport')).toBeInTheDocument()
    expect(screen.getByDisplayValue('Destination airport')).toBeInTheDocument()
  })
})
