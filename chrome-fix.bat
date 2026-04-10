@echo off
echo ============================================================
echo Chrome HTTPS Certificate Fix
echo ============================================================
echo.
echo This will open Chrome with relaxed security for development
echo WARNING: Only use this for development purposes!
echo.
echo Starting Chrome with certificate bypass...
echo.

start chrome --ignore-certificate-errors --ignore-ssl-errors --allow-running-insecure-content --disable-web-security --user-data-dir="C:\temp\chrome-dev" "https://10.180.133.44:3443"

echo.
echo Chrome started with relaxed security settings.
echo You can now access your HTTPS server without certificate warnings.
echo.
pause