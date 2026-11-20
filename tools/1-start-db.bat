@echo off
title notice-board / MySQL :3306
echo ==========================================
echo   notice-board  -  start MySQL
echo ==========================================
echo.

netstat -ano | findstr ":3306 " >nul
if not errorlevel 1 goto :already

set MYSQL_HOME=D:\phpstudy_pro\Extensions\MySQL5.7.26
if not exist "%MYSQL_HOME%\bin\mysqld.exe" goto :notfound

cd /d "%MYSQL_HOME%\bin"
echo Starting mysqld ...
echo Keep this window open. Press Ctrl+C to stop.
echo.
mysqld.exe --defaults-file="%MYSQL_HOME%\my.ini" --console

echo.
echo [mysql stopped]
pause
exit /b

:already
echo [OK] Port 3306 is already in use.
echo      MySQL seems to be running already. Nothing to do.
echo      Next: run 2-start-api.bat
echo.
pause
exit /b

:notfound
echo [ERROR] mysqld.exe not found at:
echo         %MYSQL_HOME%\bin\mysqld.exe
echo.
echo This script assumes phpStudy is installed on D:.
echo If you use XAMPP, WAMP or a standalone MySQL, start it with
echo that tool instead, then run 2-start-api.bat.
echo.
pause
exit /b
