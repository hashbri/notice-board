@echo off
title notice-board / API :8000
echo ==========================================
echo   notice-board  -  PHP backend on :8000
echo ==========================================
echo.

cd /d "%~dp0..\backend"
if errorlevel 1 goto :badcd

netstat -ano | findstr ":8000 " | findstr "LISTENING" >nul
if not errorlevel 1 goto :portbusy

netstat -ano | findstr ":3306 " | findstr "LISTENING" >nul
if errorlevel 1 (
    echo [WARN] Port 3306 is not listening. MySQL may not be running.
    echo        Start it first with 1-start-db.bat, or the API will
    echo        return a connection error.
    echo.
)

set PHP_EXE=php
where php >nul 2>nul
if not errorlevel 1 goto :havephp

echo [INFO] php not found in PATH. Trying phpStudy default path...
set PHP_EXE=D:\phpstudy_pro\Extensions\php\php7.4.33nts\php.exe
if exist "%PHP_EXE%" goto :havephp

echo [ERROR] php.exe not found in PATH either at:
echo         %PHP_EXE%
echo.
echo Install PHP 7.4+ and add it to PATH, or edit PHP_EXE in
echo this file to point at your php.exe.
echo.
pause
exit /b

:havephp
echo Using : %PHP_EXE%
echo Open  : http://localhost:8000/api/articles
echo.
echo Keep this window open. Press Ctrl+C to stop.
echo.
"%PHP_EXE%" -S localhost:8000 -t public public/router.php

echo.
echo [server stopped]
pause
exit /b

:badcd
echo [ERROR] Cannot enter the backend folder.
echo         Expected next to this script: ..\backend
echo.
pause
exit /b

:portbusy
echo [ERROR] Port 8000 is already in use.
echo         An old backend window is probably still open.
echo         Close it, then run this file again.
echo.
pause
exit /b
