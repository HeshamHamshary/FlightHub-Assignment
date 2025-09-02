// Environment configuration
// Automatically detects local vs production environment
// - Development: http://127.0.0.1:8000/api (localhost)
// - Production: https://flight-trip-backend-3nxy4.ondigitalocean.app/api (DigitalOcean)
export const config = {
  API_URL_BASE: import.meta.env.VITE_API_URL_BASE ||
    (import.meta.env.DEV ? 'http://127.0.0.1:8000/api' : 'https://flight-trip-backend-3nxy4.ondigitalocean.app/api'),
} as const

// API endpoints
export const API_ENDPOINTS = {
  FLIGHT_SEARCH: `${config.API_URL_BASE}/flight/search`,
} as const
