@echo off
echo ============================================================
echo Ngrok Certificate Fix
echo ============================================================
echo.

echo Trying different solutions...
echo.

echo [1/3] Stopping ngrok...
taskkill /F /IM ngrok.exe 2>nul
timeout /t 2 /nobreak >nul

echo [2/3] Clearing ngrok cache...
rd /s /q "%USERPROFILE%\AppData\Local\ngrok" 2>nul
timeout /t 1 /nobreak >nul

echo [3/3] Re-configuring auth token...
.\ngrok.exe config add-authtoken 34KcoJUaf3Jw6PEjxdgT8wnU3nL_39n8GEMK7YGtbq3FJdDDH

echo.
echo ============================================================
echo Fix applied! Now trying to start ngrok...
echo ============================================================
echo.

timeout /t 2 /nobreak >nul

.\ngrok.exe http 5000

pause
