@echo off
echo ========================================
echo   MySQL Speed Fix Script
echo ========================================
echo.
echo This script will:
echo 1. Stop MySQL service
echo 2. Update MySQL configuration
echo 3. Update Windows hosts file
echo 4. Restart MySQL service
echo.
echo You need to run this as Administrator!
echo.
pause

echo.
echo Step 1: Stopping MySQL...
taskkill /F /IM mysqld.exe 2>nul
timeout /t 2 >nul

echo Step 2: Backing up original files...
copy "C:\xampp\mysql\bin\my.ini" "C:\xampp\mysql\bin\my.ini.backup" >nul 2>&1
copy "C:\Windows\System32\drivers\etc\hosts" "C:\Windows\System32\drivers\etc\hosts.backup" >nul 2>&1

echo Step 3: Copying fixed MySQL configuration...
copy /Y "mysql-config-backup.ini" "C:\xampp\mysql\bin\my.ini"

echo Step 4: Copying fixed hosts file...
copy /Y "hosts-backup.txt" "C:\Windows\System32\drivers\etc\hosts"

echo Step 5: Starting MySQL with new configuration...
start "" "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini"

timeout /t 3 >nul

echo.
echo ========================================
echo   Configuration Updated!
echo ========================================
echo.
echo Changes made:
echo  - Added skip-name-resolve to MySQL
echo  - Added skip-host-cache to MySQL  
echo  - Enabled localhost in hosts file
echo  - Bound MySQL to 127.0.0.1
echo.
echo MySQL should now connect MUCH faster!
echo.
echo Test it by opening:
echo http://localhost/attendance-system/login.php
echo.
pause
