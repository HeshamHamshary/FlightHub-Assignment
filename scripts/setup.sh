#!/bin/bash

# FlightHub Assignment - Automated Setup Script
# Works on macOS and Linux

set -e  # Exit on any error

# Get the directory where this script is located
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

echo "🚀 FlightHub Assignment - Automated Setup"
echo "========================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Check prerequisites
print_status "Checking prerequisites..."

# Check PHP
if command_exists php; then
    PHP_VERSION=$(php -v | head -n1 | cut -d' ' -f2 | cut -d'.' -f1,2)
    print_success "PHP $PHP_VERSION found"
    if ! php -v | grep -q "8\.[2-9]\|8\.1[0-9]\|9\."; then
        print_warning "PHP 8.2+ recommended, but continuing with $PHP_VERSION"
    fi
else
    print_error "PHP not found. Please install PHP 8.2+ first."
    exit 1
fi

# Check Composer
if command_exists composer; then
    print_success "Composer found"
else
    print_error "Composer not found. Please install Composer first."
    exit 1
fi

# Check Node.js
if command_exists node; then
    NODE_VERSION=$(node -v)
    print_success "Node.js $NODE_VERSION found"
else
    print_error "Node.js not found. Please install Node.js 18+ first."
    exit 1
fi

# Check npm
if command_exists npm; then
    print_success "npm found"
else
    print_error "npm not found. Please install npm first."
    exit 1
fi

print_success "All prerequisites satisfied!"
echo ""

# PHP configuration guidance
print_status "Note: If you encounter 'ext-fileinfo' errors during PHP dependency installation,"
print_status "you may need to enable the fileinfo extension in your php.ini file."
print_status "Common locations: /etc/php/*/cli/php.ini or /usr/local/etc/php.ini"
print_status "Change: ;extension=fileinfo to extension=fileinfo"
echo ""

print_status "Note: If you encounter 'could not find driver' errors for SQLite,"
print_status "you may need to enable SQLite extensions in your php.ini file."
print_status "Change: ;extension=pdo_sqlite to extension=pdo_sqlite"
print_status "Change: ;extension=sqlite3 to extension=sqlite3"
print_status "Then restart your system or PHP processes."
echo ""

# Setup Backend
print_status "Setting up Backend (Laravel API)..."
cd "$PROJECT_ROOT/apps/Backend"

print_status "Installing PHP dependencies..."
composer update --quiet

print_status "Setting up environment..."
if [ ! -f .env ]; then
    cp .env.example .env
    print_success "Environment file created"
else
    print_warning ".env file already exists, skipping"
fi

print_status "Generating application key..."
php artisan key:generate --quiet

print_status "Setting up SQLite database..."
if [ ! -f "database/database.sqlite" ]; then
    print_status "Creating SQLite database file..."
    touch "database/database.sqlite"
    print_success "SQLite database file created"
else
    print_warning "SQLite database file already exists, skipping"
fi

print_status "Running database migrations..."
php artisan migrate --quiet

print_status "Checking if database needs seeding..."
FLIGHT_COUNT=$(php artisan tinker --execute="echo App\Models\Flight::count();" 2>/dev/null | tr -d '\n' || echo "")

if [ -z "$FLIGHT_COUNT" ] || [ "$FLIGHT_COUNT" -lt 1000 ]; then
    print_status "Seeding database with sample data..."
    print_warning "This may take 1-2 minutes for 100,000+ flight records..."
    print_status "Seeding progress will be shown below..."
    php artisan db:seed --verbose
else
    print_success "Database already seeded with $FLIGHT_COUNT flights, skipping seeding"
fi

print_success "Backend setup complete!"

# Setup Frontend
print_status "Setting up Frontend (React App)..."
cd "$PROJECT_ROOT/apps/Frontend"

print_status "Installing Node.js dependencies..."
npm install --silent

print_success "Frontend setup complete!"

echo ""
print_success "🎉 Setup Complete!"
echo ""
echo "To start the application: execute the start.sh script"

