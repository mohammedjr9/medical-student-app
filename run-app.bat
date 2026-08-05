@echo off
title Medical Student Registration App
cd /d "%~dp0"
echo ============================================================
echo   Running Medical Student Registration Laravel Server
echo ============================================================
echo   App URL: http://127.0.0.1:8000
echo ============================================================
php artisan serve
pause
