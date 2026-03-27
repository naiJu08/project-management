@echo off
echo Starting Socket.IO server for video calls...
echo.
cd /d "%~dp0"
node server.js
pause
