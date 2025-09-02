@echo off
setlocal

REM FlightHub Assignment - Start Script for Windows
REM Starts both backend and frontend servers

REM Get the directory where this script is located
set SCRIPT_DIR=%~dp0
set PROJECT_ROOT=%SCRIPT_DIR%..

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
cd "%PROJECT_ROOT%\apps\Backend"

REM Check if database has flights (more robust method)
echo [INFO] Verifying database connection...
php artisan tinker --execute="echo App\Models\Flight::count();" > temp_count.txt 2>nul
if errorlevel 1 (
    echo.
    echo ❌ [ERROR] Failed to connect to database!
    echo ❌ [ERROR] Please check your .env file and database connection.
    echo.
    del temp_count.txt
    pause
    exit /b 1
)

set /p FLIGHT_COUNT=<temp_count.txt
del temp_count.txt

if "%FLIGHT_COUNT%"=="" (
    echo.
    echo ❌ [ERROR] Database not set up or has insufficient data!
    echo ❌ [ERROR] Please run scripts\setup.bat first to set up the database.
    echo.
    echo Run this command:
    echo   scripts\setup.bat
    echo.
    pause
    exit /b 1
)

if %FLIGHT_COUNT% LSS 100 (
    echo.
    echo ⚠️ [WARNING] Database has only %FLIGHT_COUNT% flights
    echo ⚠️ [WARNING] Consider running setup.bat to seed more data
    echo.
)

echo [SUCCESS] ✅ Database ready with %FLIGHT_COUNT% flights

echo [INFO] Starting Backend on port %BACKEND_PORT%...
start "FlightHub Backend" cmd /k "cd /d "%PROJECT_ROOT%\apps\Backend" && php artisan serve --port=%BACKEND_PORT%"

REM Wait for backend to start
echo [INFO] Waiting for backend to start...
timeout /t 3 /nobreak >nul

echo [INFO] Starting Frontend...
cd "%PROJECT_ROOT%\apps\Frontend"

REM Start frontend in new window
start "FlightHub Frontend" cmd /k "cd /d "%PROJECT_ROOT%\apps\Frontend" && npm run dev"

REM Wait for frontend to start
echo [INFO] Waiting for frontend to start...
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
echo 💡 Tip: If you see any errors, check the command windows for details.
echo.
pause
