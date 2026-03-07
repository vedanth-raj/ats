@echo off
echo ========================================
echo Database Configuration Helper
echo ========================================
echo.
echo Current Configuration:
echo   Server: localhost
echo   Database: management_auto_attendance_system
echo   Username: root
echo   Password: (empty)
echo.
echo ========================================
echo.

set /p CHANGE="Do you want to change the database password? (Y/N): "

if /i "%CHANGE%"=="Y" (
    set /p NEW_PASSWORD="Enter your MySQL root password: "
    
    echo.
    echo Updating configuration file...
    
    REM Create a temporary PowerShell script to update XML
    echo $configPath = "0-management-auto-attendance-system\Management_Auto_Attendance_System\App.config" > temp_update.ps1
    echo [xml]$xml = Get-Content $configPath >> temp_update.ps1
    echo $setting = $xml.configuration.userSettings.'Management_Auto_Attendance_System.Properties.Settings'.setting ^| Where-Object { $_.name -eq 'server_password' } >> temp_update.ps1
    echo $setting.value = "%NEW_PASSWORD%" >> temp_update.ps1
    echo $xml.Save($configPath) >> temp_update.ps1
    
    powershell -ExecutionPolicy Bypass -File temp_update.ps1
    del temp_update.ps1
    
    echo.
    echo ========================================
    echo Configuration updated successfully!
    echo ========================================
    echo.
    echo New Settings:
    echo   Server: localhost
    echo   Database: management_auto_attendance_system
    echo   Username: root
    echo   Password: %NEW_PASSWORD%
    echo.
) else (
    echo.
    echo No changes made.
    echo.
)

pause
