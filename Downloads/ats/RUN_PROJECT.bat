@echo off
echo ========================================
echo Management Auto Attendance System
echo Quick Run Script
echo ========================================
echo.

REM Check if the executable exists
if not exist "0-management-auto-attendance-system\Management_Auto_Attendance_System\bin\Debug\Management_Auto_Attendance_System.exe" (
    echo Building project for the first time...
    echo This may take a few moments...
    echo.
    
    REM Build the project
    "C:\Program Files\Microsoft Visual Studio\2022\Community\MSBuild\Current\Bin\MSBuild.exe" "0-management-auto-attendance-system\Management_Auto_Attendance_System.sln" /p:Configuration=Debug /t:Build /v:minimal
    
    if %ERRORLEVEL% NEQ 0 (
        echo.
        echo ========================================
        echo BUILD FAILED!
        echo ========================================
        echo.
        echo Please open the project in Visual Studio and check for errors.
        pause
        exit /b 1
    )
    
    echo.
    echo Build completed successfully!
    echo.
)

echo Starting Management Auto Attendance System...
echo.
echo Default Login Credentials:
echo   Username: admin
echo   Password: kuna123
echo.
echo ========================================
echo.

REM Run the application
start "" "0-management-auto-attendance-system\Management_Auto_Attendance_System\bin\Debug\Management_Auto_Attendance_System.exe"

echo Application started!
echo You can close this window.
echo.
timeout /t 3
