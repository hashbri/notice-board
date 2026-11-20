@echo off
title notice-board / WEB :5500
echo ==========================================
echo   notice-board  -  static frontend on :5500
echo ==========================================
echo.

cd /d "%~dp0..\frontend"
if errorlevel 1 goto :badcd

netstat -ano | findstr ":5500 " >nul
if not errorlevel 1 goto :portbusy

set PHP_EXE=php
where php >nul 2>nul
if not errorlevel 1 goto :havephp

echo [INFO] php not found in PATH. Trying phpStudy default path...
set PHP_EXE=D:\phpstudy_pro\Extensions\php\php7.4.33nts\php.exe
if exist "%PHP_EXE%" goto :havephp

echo [ERROR] php.exe not found.
echo         Install PHP 7.4+ and add it to PATH, or edit PHP_EXE
echo         in this file.
echo.
pause
exit /b

:havephp
echo Using : %PHP_EXE%
echo Open  : http://localhost:5500/index.html
echo.
echo Keep this window open. Press Ctrl+C to stop.
echo.
"%PHP_EXE%" -S localhost:5500 -t .

echo.
echo [frontend stopped]
pause
exit /b

:badcd
echo [ERROR] Cannot enter the frontend folder.
echo         Expected next to this script: ..\frontend
echo.
pause
exit /b

:portbusy
echo [ERROR] Port 5500 is already in use.
echo         Close the old window or change the port in this file
echo         AND in the three HTML files.
echo.
pause
exit /b
