@echo off
color 0A
echo ========================================
echo   Web Application Setup
echo   Management Auto Attendance System
echo ========================================
echo.

REM Check if XAMPP is installed
if not exist "C:\xampp\htdocs" (
    echo ERROR: XAMPP not found at C:\xampp
    echo Please install XAMPP first from https://www.apachefriends.org/
    pause
    exit /b 1
)

echo Step 1: Copying web application files to XAMPP...
echo.

REM Create directory in htdocs
if not exist "C:\xampp\htdocs\attendance-system" (
    mkdir "C:\xampp\htdocs\attendance-system"
)

REM Copy web-app files
xcopy /E /I /Y "web-app\*" "C:\xampp\htdocs\attendance-system\"

if %ERRORLEVEL% EQU 0 (
    echo Files copied successfully!
    echo.
) else (
    echo ERROR: Failed to copy files
    pause
    exit /b 1
)

echo Step 2: Starting XAMPP services...
echo.

REM Start Apache and MySQL
"C:\xampp\xampp-control.exe"

echo.
echo ========================================
echo   SETUP COMPLETE!
echo ========================================
echo.
echo Your web application is now ready!
echo.
echo Access it at: http://localhost/attendance-system
echo.
echo Default Login:
echo   Username: admin
echo   Password: kuna123
echo.
echo IMPORTANT:
echo 1. Make sure Apache and MySQL are running in XAMPP Control Panel
echo 2. Database is already configured with your MySQL password
echo.
echo Opening browser in 5 seconds...
timeout /t 5

REM Open browser
start http://localhost/attendance-system

echo.
echo You can close this window now.
pause
