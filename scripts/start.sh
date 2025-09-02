#!/bin/bash

# FlightHub Assignment - Start Script
# Starts both backend and frontend servers simultaneously

set -e

# Get the directory where this script is located
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

echo "🚀 Starting FlightHub Assignment..."
echo "=================================="

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

# Check if already running
if lsof -i :8000 >/dev/null 2>&1; then
    print_info "Port 8000 already in use, trying 8001..."
    BACKEND_PORT=8001
else
    BACKEND_PORT=8000
fi

# Function to kill background processes on exit
cleanup() {
    print_info "Shutting down servers..."
    jobs -p | xargs -r kill
    exit
}

# Set trap to cleanup on script exit
trap cleanup INT TERM EXIT

# Check if database is set up
print_info "Checking database setup..."
cd "$PROJECT_ROOT/apps/Backend"

if ! php artisan tinker --execute="echo App\Models\Flight::count();" 2>/dev/null | grep -E "^[0-9]+$" >/dev/null 2>&1; then
    echo ""
    echo "❌ [ERROR] Database not set up or has insufficient data!"
    echo "❌ [ERROR] Please run ./scripts/setup.sh first to set up the database."
    echo ""
    echo "Run this command:"
    echo "  ./scripts/setup.sh"
    echo ""
    exit 1
fi

FLIGHT_COUNT=$(php artisan tinker --execute="echo App\Models\Flight::count();" 2>/dev/null | grep -E "^[0-9]+$" | head -1)
print_success "Database ready with $FLIGHT_COUNT flights"

# Start backend in background
print_info "Starting Backend on port $BACKEND_PORT..."
php artisan serve --port=$BACKEND_PORT &
BACKEND_PID=$!

# Wait a moment for backend to start
sleep 2

# Start frontend in background
print_info "Starting Frontend..."
cd "$PROJECT_ROOT/apps/Frontend"
npm run dev &
FRONTEND_PID=$!

# Wait a moment for frontend to start
sleep 3

echo ""
print_success "🎉 Servers started successfully!"
echo ""
echo "📱 Frontend: http://localhost:5173"
echo "🔧 Backend API: http://127.0.0.1:$BACKEND_PORT"
echo ""
echo "Press Ctrl+C to stop both servers"
echo ""

# Wait for background processes
wait
