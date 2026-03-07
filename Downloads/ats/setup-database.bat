@echo off
echo ========================================
echo Management Auto Attendance System
echo Database Setup Script
echo ========================================
echo.

REM Prompt for MySQL password
set /p MYSQL_PASSWORD="Enter your MySQL root password (press Enter if no password): "

echo.
echo Creating database and importing schema...
echo.

REM Create database and import SQL file
if "%MYSQL_PASSWORD%"=="" (
    mysql -u root -e "CREATE DATABASE IF NOT EXISTS management_auto_attendance_system;"
    mysql -u root management_auto_attendance_system < "1-database\management_auto_attendance_system.sql"
) else (
    mysql -u root -p%MYSQL_PASSWORD% -e "CREATE DATABASE IF NOT EXISTS management_auto_attendance_system;"
    mysql -u root -p%MYSQL_PASSWORD% management_auto_attendance_system < "1-database\management_auto_attendance_system.sql"
)

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo SUCCESS! Database setup completed.
    echo ========================================
    echo.
    echo Database: management_auto_attendance_system
    echo Default Login:
    echo   Username: admin
    echo   Password: kuna123
    echo.
) else (
    echo.
    echo ========================================
    echo ERROR! Database setup failed.
    echo ========================================
    echo.
    echo Please check:
    echo 1. MySQL Server is running
    echo 2. MySQL root password is correct
    echo 3. MySQL is in your system PATH
    echo.
)

pause
