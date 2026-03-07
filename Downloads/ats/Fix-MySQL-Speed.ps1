# MySQL Speed Fix Script
# Run this as Administrator

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  MySQL Speed Fix Script" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if running as admin
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)

if (-not $isAdmin) {
    Write-Host "ERROR: This script must be run as Administrator!" -ForegroundColor Red
    Write-Host "Right-click PowerShell and select 'Run as Administrator'" -ForegroundColor Yellow
    pause
    exit
}

Write-Host "Step 1: Stopping MySQL..." -ForegroundColor Yellow
Stop-Process -Name "mysqld" -Force -ErrorAction SilentlyContinue
Start-Sleep -Seconds 3

Write-Host "Step 2: Backing up original files..." -ForegroundColor Yellow
Copy-Item "C:\xampp\mysql\bin\my.ini" "C:\xampp\mysql\bin\my.ini.backup" -Force -ErrorAction SilentlyContinue
Copy-Item "C:\Windows\System32\drivers\etc\hosts" "C:\Windows\System32\drivers\etc\hosts.backup" -Force -ErrorAction SilentlyContinue

Write-Host "Step 3: Applying MySQL configuration fix..." -ForegroundColor Yellow
Copy-Item "mysql-config-backup.ini" "C:\xampp\mysql\bin\my.ini" -Force

Write-Host "Step 4: Applying hosts file fix..." -ForegroundColor Yellow
Copy-Item "hosts-backup.txt" "C:\Windows\System32\drivers\etc\hosts" -Force

Write-Host "Step 5: Starting MySQL with new configuration..." -ForegroundColor Yellow
Start-Process "C:\xampp\mysql_start.bat" -WindowStyle Hidden
Start-Sleep -Seconds 5

# Verify MySQL is running
$mysqlProcess = Get-Process -Name "mysqld" -ErrorAction SilentlyContinue
if ($mysqlProcess) {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Green
    Write-Host "  SUCCESS! MySQL is running" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "Changes applied:" -ForegroundColor Cyan
    Write-Host "  - Added skip-name-resolve to MySQL"
    Write-Host "  - Added skip-host-cache to MySQL"
    Write-Host "  - Enabled localhost in hosts file"
    Write-Host "  - Bound MySQL to 127.0.0.1"
    Write-Host ""
    Write-Host "MySQL should now connect MUCH faster!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Test it by opening:" -ForegroundColor Yellow
    Write-Host "http://localhost/attendance-system/login.php" -ForegroundColor Cyan
} else {
    Write-Host ""
    Write-Host "WARNING: MySQL may not have started properly" -ForegroundColor Yellow
    Write-Host "Please start MySQL manually from XAMPP Control Panel" -ForegroundColor Yellow
}

Write-Host ""
pause
