// Environment configuration
export const config = {
  API_URL_BASE: import.meta.env.VITE_API_URL_BASE || 'http://localhost:8000/api',
} as const

// API endpoints
export const API_ENDPOINTS = {
  FLIGHT_SEARCH: `${config.API_URL_BASE}/flight/search`,
} as const
