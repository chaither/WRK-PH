@echo off
REM Automatic Biometric Sync - Run this to start automatic sync
REM This will sync biometric attendances every 10 seconds automatically

cd /d "%~dp0"
echo Starting automatic biometric sync...
echo This will sync attendances every 10 seconds
echo Press Ctrl+C to stop
echo.

php artisan zkteco:sync-continuous --interval=10

