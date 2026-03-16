@echo off
echo Adding CMake to system PATH...
setx PATH "%PATH%;C:\Program Files\CMake\bin"
echo CMake added to PATH. Please restart your command prompt/PowerShell.
echo You can verify by running: cmake --version
pause