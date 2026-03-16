@echo off
echo Setting up Windows Firewall for UAS Network Access...
echo.

REM Check if running as administrator
net session >nul 2>&1
if %errorLevel% == 0 (
    echo Running as Administrator - Good!
    echo.
) else (
    echo ERROR: This script must be run as Administrator
    echo Right-click on this file and select "Run as administrator"
    echo.
    pause
    exit /b 1
)

echo Adding Windows Firewall rule for UAS Server (Port 3000)...
netsh advfirewall firewall add rule name="UAS Server - Port 3000" dir=in action=allow protocol=TCP localport=3000

if %errorLevel% == 0 (
    echo.
    echo ✅ SUCCESS: Firewall rule added successfully!
    echo.
    echo Your UAS server can now be accessed from other devices on your network.
    echo.
    echo Network URLs:
    echo - http://10.180.133.44:3000
    echo - http://192.168.56.1:3000
    echo.
    echo Next steps:
    echo 1. Start your server: npm start
    echo 2. Connect other devices to the same WiFi
    echo 3. Open browser and go to the network URL
    echo.
) else (
    echo.
    echo ❌ ERROR: Failed to add firewall rule
    echo Please check your administrator privileges
    echo.
)

pause