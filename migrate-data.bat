@echo off
echo ========================================
echo MIGRASI DATA JSON KE DATABASE
echo ========================================
echo.

echo [1/2] Menjalankan migrasi data...
php migrate-json-to-db.php

echo.
echo ========================================
echo SELESAI!
echo ========================================
pause
