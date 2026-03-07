@echo off
color 0A
echo ========================================
echo   Management Auto Attendance System
echo   Web Application Launcher
echo ========================================
echo.
echo Opening web application in browser...
echo.
echo URL: http://localhost/attendance-system
echo.
echo Default Login:
echo   Username: admin
echo   Password: kuna123
echo.
echo ========================================
echo.

start http://localhost/attendance-system

timeout /t 2
exit
