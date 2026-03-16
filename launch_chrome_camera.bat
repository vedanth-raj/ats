@echo off
echo 🚀 Launching Chrome with Camera Access for Network IP...
echo.
echo This will open Chrome with special flags to allow camera access
echo on the network IP address: http://10.180.133.44:3000
echo.
echo ⚠️  This creates a temporary Chrome profile for testing.
echo    Your regular Chrome settings won't be affected.
echo.

REM Create temp directory for Chrome profile
if not exist "C:\temp" mkdir "C:\temp"
if not exist "C:\temp\chrome-camera" mkdir "C:\temp\chrome-camera"

echo Starting Chrome with camera permissions...
echo.

REM Launch Chrome with insecure origins flag
start chrome.exe --unsafely-treat-insecure-origin-as-secure=http://10.180.133.44:3000 --user-data-dir=C:\temp\chrome-camera --new-window http://10.180.133.44:3000

echo.
echo ✅ Chrome launched with camera access enabled!
echo.
echo 📋 Instructions:
echo 1. Chrome should open automatically with the UAS homepage
echo 2. Go to Employee Registration or Attendance
echo 3. Click "Use Camera" - it should work now!
echo 4. Allow camera permissions when prompted
echo.
echo 🔗 Direct links to test:
echo - Employee Registration: http://10.180.133.44:3000/employee-registration.html
echo - Attendance: http://10.180.133.44:3000/attendance.html
echo - Camera Test: http://10.180.133.44:3000/camera-test.html
echo.
pause