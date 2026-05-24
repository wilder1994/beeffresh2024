@echo off
cd /d "%~dp0.."
if exist public\hot del /f public\hot
echo Building assets...
call npm run build
if errorlevel 1 exit /b 1
echo Clearing config...
"C:\laragon\bin\php\php-8.2.29-Win32-vs16-x64\php.exe" artisan config:clear
echo.
echo Listo. Recarga la URL ngrok con Ctrl+F5.
pause
