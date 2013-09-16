@echo off

rem -------------------------------------------------------------
rem  ThougtYards command line script for Windows.
rem  You are using the "ThougtYards" framework behind the scenes, is designed by Vipul Dadhich
rem -------------------------------------------------------------

@setlocal

set BIN_PATH=%~dp0

if "%PHP_COMMAND%" == "" set PHP_COMMAND=php.exe

"%PHP_COMMAND%" "%BIN_PATH%console.php" %*

@endlocal