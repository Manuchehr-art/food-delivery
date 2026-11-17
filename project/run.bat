@echo off
chcp 65001 > nul
title Болаззат - Запуск веб-сервера доставки еды
echo ============================================================
echo   Запуск ИС "Болаззат" (Служба доставки еды)
echo   Архитектура: PHP MVC ^| СУБД: MySQL / phpMyAdmin
echo ============================================================
echo.

set PHP_BIN=php
where php >nul 2>nul
if %errorlevel% neq 0 (
    if exist "D:\php\php.exe" (
        set PHP_BIN=D:\php\php.exe
    ) else (
        echo Ошибка: PHP не найден в системе. Убедитесь, что PHP установлен.
        pause
        exit /b 1
    )
)

echo PHP найден: %PHP_BIN%
echo Веб-сервер запускается на http://localhost:8000 ...
echo Нажмите Ctrl+C для остановки сервера.
echo.

start "" "http://localhost:8000"
"%PHP_BIN%" -c "%~dp0php.ini" -S localhost:8000 -t "%~dp0public"

pause
