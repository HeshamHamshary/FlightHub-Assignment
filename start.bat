@echo off
setlocal

REM FlightHub Assignment - Start Script for Windows
REM Starts both backend and frontend servers

echo.
echo 🚀 Starting FlightHub Assignment...
echo ==================================
echo.

REM Check if port 8000 is available
netstat -an | find ":8000" >nul
if errorlevel 1 (
    set BACKEND_PORT=8000
) else (
    echo [INFO] Port 8000 already in use, trying 8001...
    set BACKEND_PORT=8001
)

echo [INFO] Checking database setup...
cd apps\Backend

REM Check if database has flights
php artisan tinker --execute="echo App\Models\Flight::count();" > temp_count.txt 2>nul
set /p FLIGHT_COUNT=<temp_count.txt
del temp_count.txt

if "%FLIGHT_COUNT%"=="" (
    echo.
    echo ❌ [ERROR] Database not set up or has insufficient data!
    echo ❌ [ERROR] Please run setup.bat first to set up the database.
    echo.
    echo Run this command:
    echo   setup.bat
    echo.
    pause
    exit /b 1
)

echo [SUCCESS] ✅ Database ready with %FLIGHT_COUNT% flights

echo [INFO] Starting Backend on port %BACKEND_PORT%...
start "FlightHub Backend" cmd /k "php artisan serve --port=%BACKEND_PORT%"

REM Wait for backend to start
timeout /t 3 /nobreak >nul

echo [INFO] Starting Frontend...
cd ..\Frontend

REM Start frontend in new window
start "FlightHub Frontend" cmd /k "npm run dev"

REM Wait for frontend to start
timeout /t 3 /nobreak >nul

echo.
echo [SUCCESS] 🎉 Servers started successfully!
echo.
echo 📱 Frontend: http://localhost:5173
echo 🔧 Backend API: http://127.0.0.1:%BACKEND_PORT%
echo.
echo Both servers are running in separate windows.
echo Close the command windows to stop the servers.
echo.
pause
