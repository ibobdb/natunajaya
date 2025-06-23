@echo off
echo ===============================================
echo     Natuna Jaya Payment Notification Tests    
echo ===============================================
echo.

php test_all_payment_notifications.php

if %ERRORLEVEL% EQU 0 (
    echo.
    echo Tests completed successfully!
) else (
    echo.
    echo Tests failed with status code: %ERRORLEVEL%
)

echo.
echo Press any key to exit...
pause > nul
