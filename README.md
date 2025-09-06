# Flight Trip Builder

A full-stack flight search application built with Laravel (PHP) and React (TypeScript).

## Quick Start

### Prerequisites
- PHP 8.2+, Composer, Node.js 18+, Git

### 🚀 Automated Setup (Recommended)

**Option 1: One-command setup**
```bash
# Clone and setup everything automatically
git clone <your-repo-url>
cd FlightHub-Assignment

# macOS/Linux
./scripts/setup.sh

# Windows
scripts/setup.bat
```

### Running the Application

**Automated start (both servers)**
```bash
# macOS/Linux
./scripts/start.sh

# Windows  
scripts/start.bat
```



### Local Access
- **Frontend**: http://localhost:5173 (Vite default)
- **Backend API**: http://127.0.0.1:8000 (or 8001 if 8000 is busy)

> **Note**: The start scripts automatically detect available ports and display the correct URLs when servers start.


### Production Access
- **Frontend**: [Hosted on Vercel](https://flight-trips-frontend-bzuys9hts-heshamhamshary-5041s-projects.vercel.app/)
- **Backend API**: [Hosted on Digital Ocean](https://flight-trip-backend-3nxy4.ondigitalocean.app/)

## Documentation

For detailed technical information, API endpoints, and architecture details, see [DOCUMENTATION.md](./DOCUMENTATION.md).


## Troubleshooting

### PHP Dependencies Installation Issues

If you encounter errors during PHP dependency installation (like missing extensions), you'll need to enable required PHP extensions in your `php.ini` file.

#### **Step 1: Locate your php.ini file**
```bash
# Run this command to find your php.ini location
php --ini
```

**Common locations:**
- **Windows**: `C:\Program Files\php\php.ini`
- **macOS**: `/usr/local/etc/php/8.x/php.ini` or `/etc/php.ini`
- **Linux**: `/etc/php/8.x/cli/php.ini` or `/etc/php.ini`

#### **Step 2: Enable required extensions**
Open your `php.ini` file in a text editor and uncomment (remove the semicolon) these lines:

```ini
; Enable these extensions by removing the semicolon (;) at the start
extension=fileinfo
extension=pdo_sqlite
extension=sqlite3
extension=curl
extension=openssl
extension=zip
extension=mbstring
```

**What each extension does:**
- `fileinfo`: Required by Laravel for file handling
- `pdo_sqlite`: Database connectivity for SQLite
- `sqlite3`: SQLite database support
- `curl`: HTTP requests (helps with Composer downloads)
- `openssl`: Secure connections and encryption
- `zip`: Package extraction (speeds up Composer)
- `mbstring`: Multi-byte string handling

#### **Step 3: Restart and retry**
1. Save the `php.ini` file
2. **Restart your terminal/PowerShell** (or restart your system)
3. Run the setup script again
