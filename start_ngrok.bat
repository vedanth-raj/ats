@echo off
echo ============================================================
echo LitWise AI - Quick Start with Ngrok
echo ============================================================
echo.

echo [1/2] Starting Flask Application...
start "Flask App" cmd /k python web_app.py

echo Waiting for Flask to start...
timeout /t 8 /nobreak >nul

echo.
echo [2/2] Starting Ngrok Tunnel...
echo.
echo ============================================================
echo YOUR PUBLIC URL WILL APPEAR BELOW
echo Look for "Forwarding" line (https://xxxxx.ngrok-free.dev)
echo ============================================================
echo.

.\ngrok.exe http 5000

pause
