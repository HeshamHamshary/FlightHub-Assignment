@echo off
setlocal enabledelayedexpansion

REM FlightHub Assignment - Automated Setup Script for Windows

REM Get the directory where this script is located
set SCRIPT_DIR=%~dp0
set PROJECT_ROOT=%SCRIPT_DIR%..

echo.
echo 🚀 FlightHub Assignment - Automated Setup
echo ========================================
echo.

REM Check PHP
echo [INFO] Checking prerequisites...
where php >nul 2>nul
if errorlevel 1 (
    echo [ERROR] PHP not found. Please install PHP 8.2+ first.
    echo Download from: https://www.php.net/downloads.php
    pause
    exit /b 1
) else (
    echo [SUCCESS] PHP found
)

REM Check Composer
where composer >nul 2>nul
if errorlevel 1 (
    echo [ERROR] Composer not found. Please install Composer first.
    echo Download from: https://getcomposer.org/download/
    pause
    exit /b 1
) else (
    echo [SUCCESS] Composer found
)

REM Check Node.js
where node >nul 2>nul
if errorlevel 1 (
    echo [ERROR] Node.js not found. Please install Node.js 18+ first.
    echo Download from: https://nodejs.org/
    pause
    exit /b 1
) else (
    for /f "tokens=*" %%i in ('node -v') do set NODE_VERSION=%%i
    echo [SUCCESS] Node.js !NODE_VERSION! found
    
    REM Check Node.js version (18+)
    for /f "tokens=2 delims=." %%i in ('node -v') do set NODE_MAJOR=%%i
    if !NODE_MAJOR! LSS 18 (
        echo [WARNING] Node.js 18+ recommended, but continuing with !NODE_VERSION!
    )
)

REM Check npm
where npm >nul 2>nul
if errorlevel 1 (
    echo [ERROR] npm not found. Please install npm first.
    pause
    exit /b 1
) else (
    echo [SUCCESS] npm found
)

REM Check Git
where git >nul 2>nul
if errorlevel 1 (
    echo [ERROR] Git not found. Please install Git first.
    echo Download from: https://git-scm.com/download/win
    pause
    exit /b 1
) else (
    echo [SUCCESS] Git found
)

echo [SUCCESS] All prerequisites satisfied!
echo.

REM Setup Backend
echo [INFO] Setting up Backend (Laravel API)...
cd "%PROJECT_ROOT%\apps\Backend"

echo [INFO] Installing PHP dependencies...
echo [INFO] This may take several minutes depending on your internet connection...
echo [INFO] Progress will be shown below...
echo.
call composer update --no-interaction
if errorlevel 1 (
    echo [ERROR] Failed to install PHP dependencies
    pause
    exit /b 1
)

echo [INFO] Setting up environment...
if not exist .env (
    copy .env.example .env >nul
    echo [SUCCESS] Environment file created
) else (
    echo [WARNING] .env file already exists, skipping
)

echo [INFO] Generating application key...
call php artisan key:generate --quiet
if errorlevel 1 (
    echo [ERROR] Failed to generate application key
    pause
    exit /b 1
)

echo [INFO] Running database migrations...
call php artisan migrate --quiet
if errorlevel 1 (
    echo [ERROR] Failed to run migrations
    echo [INFO] This might be due to database connection issues
    echo [INFO] Please check your .env file database settings
    pause
    exit /b 1
)

echo [INFO] Checking if database needs seeding...
php artisan tinker --execute="echo App\Models\Flight::count();" > temp_count.txt 2>nul
set /p FLIGHT_COUNT=<temp_count.txt
del temp_count.txt

if "%FLIGHT_COUNT%"=="" (
    echo [INFO] Seeding database with sample data...
    echo [WARNING] This may take 1-2 minutes for 100,000+ flight records...
    call php artisan db:seed --quiet
    if errorlevel 1 (
        echo [ERROR] Failed to seed database
        pause
        exit /b 1
    )
) else (
    if %FLIGHT_COUNT% GTR 1000 (
        echo [SUCCESS] Database already seeded with %FLIGHT_COUNT% flights, skipping seeding
    ) else (
        echo [INFO] Seeding database with sample data...
        echo [WARNING] This may take 1-2 minutes for 100,000+ flight records...
        call php artisan db:seed --quiet
        if errorlevel 1 (
            echo [ERROR] Failed to seed database
            pause
            exit /b 1
        )
    )
)

echo [SUCCESS] Backend setup complete!

REM Setup Frontend
echo [INFO] Setting up Frontend (React App)...
cd "%PROJECT_ROOT%\apps\Frontend"

echo [INFO] Installing Node.js dependencies...
call npm install --silent
if errorlevel 1 (
    echo [ERROR] Failed to install Node.js dependencies
    pause
    exit /b 1
)

echo [SUCCESS] Frontend setup complete!

echo.
echo [SUCCESS] 🎉 Setup Complete!
echo.
echo To start the application: run the start.bat script
pause
