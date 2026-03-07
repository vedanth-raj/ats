@echo off
color 0A
echo.
echo ========================================
echo   Management Auto Attendance System
echo   Quick Start Wizard
echo ========================================
echo.
echo This wizard will help you set up and run the application.
echo.
pause

:MENU
cls
echo.
echo ========================================
echo   QUICK START MENU
echo ========================================
echo.
echo 1. Setup Database (First Time Only)
echo 2. Configure Database Password
echo 3. Build and Run Application
echo 4. Run Application (Already Built)
echo 5. View Setup Guide
echo 6. Exit
echo.
set /p choice="Enter your choice (1-6): "

if "%choice%"=="1" goto SETUP_DB
if "%choice%"=="2" goto CONFIG_DB
if "%choice%"=="3" goto BUILD_RUN
if "%choice%"=="4" goto RUN_ONLY
if "%choice%"=="5" goto VIEW_GUIDE
if "%choice%"=="6" goto EXIT
goto MENU

:SETUP_DB
cls
echo.
echo ========================================
echo   DATABASE SETUP
echo ========================================
echo.
call setup-database.bat
pause
goto MENU

:CONFIG_DB
cls
echo.
echo ========================================
echo   CONFIGURE DATABASE
echo ========================================
echo.
call configure-database.bat
pause
goto MENU

:BUILD_RUN
cls
echo.
echo ========================================
echo   BUILD AND RUN
echo ========================================
echo.
call RUN_PROJECT.bat
pause
goto MENU

:RUN_ONLY
cls
echo.
echo ========================================
echo   RUN APPLICATION
echo ========================================
echo.
if exist "0-management-auto-attendance-system\Management_Auto_Attendance_System\bin\Debug\Management_Auto_Attendance_System.exe" (
    start "" "0-management-auto-attendance-system\Management_Auto_Attendance_System\bin\Debug\Management_Auto_Attendance_System.exe"
    echo Application started!
    timeout /t 2
) else (
    echo ERROR: Application not built yet!
    echo Please use option 3 to build first.
    pause
)
goto MENU

:VIEW_GUIDE
cls
echo.
echo ========================================
echo   SETUP GUIDE
echo ========================================
echo.
if exist "SETUP_GUIDE.md" (
    start "" "SETUP_GUIDE.md"
    echo Opening setup guide...
    timeout /t 2
) else (
    echo Setup guide not found!
    pause
)
goto MENU

:EXIT
cls
echo.
echo Thank you for using Management Auto Attendance System!
echo.
timeout /t 2
exit

