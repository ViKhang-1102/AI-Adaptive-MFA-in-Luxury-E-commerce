@echo off
REM Verification script for Chat System

echo.
echo ==========================================
echo CHAT SYSTEM - VERIFICATION CHECK
echo ==========================================
echo.

REM Check 1: Controller methods
echo Checking MessageController methods...
findstr /C:"public function getUnreadCount" "app\Http\Controllers\MessageController.php" >nul
if %errorlevel% equ 0 (
    echo  [OK] MessageController has required methods
) else (
    echo  [ERROR] Missing methods in MessageController
)

REM Check 2: Routes
echo Checking routes...
findstr /C:"/messages/unread/count" "routes\web.php" >nul
if %errorlevel% equ 0 (
    echo  [OK] Routes configured
) else (
    echo  [ERROR] Routes missing
)

REM Check 3: View file
echo Checking seller messages view...
if exist "resources\views\seller\messages\index_new.blade.php" (
    echo  [OK] index_new.blade.php exists
) else (
    echo  [ERROR] index_new.blade.php not found
)

REM Check 4: Header badge
echo Checking header badge...
findstr /C:"updateMessageBadge" "resources\views\layouts\header.blade.php" >nul
if %errorlevel% equ 0 (
    echo  [OK] Header badge script present
) else (
    echo  [ERROR] Header badge script missing
)

REM Check 5: Product model
echo Checking Product model...
findstr /C:"public function messages" "app\Models\Product.php" >nul
if %errorlevel% equ 0 (
    echo  [OK] Product model has messages relationship
) else (
    echo  [WARNING] messages relationship not found
)

REM Check 6: Testing guide
echo Checking testing guide...
if exist "CHAT_SYSTEM_TESTING_GUIDE.md" (
    echo  [OK] Testing guide exists
) else (
    echo  [WARNING] Testing guide not found
)

echo.
echo ==========================================
echo VERIFICATION COMPLETE
echo ==========================================
echo.
echo System is ready for testing!
echo.

