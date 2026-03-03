# Chat System Verification Script (PowerShell)
# This script checks if all chat system components are properly configured

Write-Host ""
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "CHAT SYSTEM - VERIFICATION CHECK" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# Check 1: Controller methods exist
Write-Host -NoNewLine "MessageController methods... "
$controllerPath = "app\Http\Controllers\MessageController.php"
$controllerContent = Get-Content $controllerPath -Raw
$check1 = $controllerContent -match "public function getUnreadCount" -and `
          $controllerContent -match "public function getCustomersList" -and `
          $controllerContent -match "public function getCustomerProducts"
if ($check1) { Write-Host "✓" -ForegroundColor Green } else { Write-Host "✗" -ForegroundColor Red }

# Check 2: Routes are configured
Write-Host -NoNewLine "Routes configuration... "
$routesPath = "routes\web.php"
$routesContent = Get-Content $routesPath -Raw
$check2 = $routesContent -match "/messages/unread/count" -and `
          $routesContent -match "/messages/api/customers"
if ($check2) { Write-Host "✓" -ForegroundColor Green } else { Write-Host "✗" -ForegroundColor Red }

# Check 3: New view file exists
Write-Host -NoNewLine "Seller messages view (index_new.blade.php)... "
$check3 = Test-Path "resources\views\seller\messages\index_new.blade.php"
if ($check3) { Write-Host "✓" -ForegroundColor Green } else { Write-Host "✗" -ForegroundColor Red }

# Check 4: Header has badge script
Write-Host -NoNewLine "Header badge script... "
$headerPath = "resources\views\layouts\header.blade.php"
$headerContent = Get-Content $headerPath -Raw
$check4 = $headerContent -match "updateMessageBadge" -and `
          $headerContent -match "data-message-badge"
if ($check4) { Write-Host "✓" -ForegroundColor Green } else { Write-Host "✗" -ForegroundColor Red }

# Check 5: Product model has messages relationship
Write-Host -NoNewLine "Product model relationships... "
$productPath = "app\Models\Product.php"
$productContent = Get-Content $productPath -Raw
$check5 = $productContent -match "public function messages\(\)"
if ($check5) { Write-Host "✓" -ForegroundColor Green } else { Write-Host "✗" -ForegroundColor Red }

# Check 6: Message model has forConversation scope
Write-Host -NoNewLine "Message model scope... "
$messagePath = "app\Models\Message.php"
$messageContent = Get-Content $messagePath -Raw
$check6 = $messageContent -match "scopeForConversation"
if ($check6) { Write-Host "✓" -ForegroundColor Green } else { Write-Host "✗" -ForegroundColor Red }

# Check 7: Testing guide exists
Write-Host -NoNewLine "Testing guide... "
$check7 = Test-Path "CHAT_SYSTEM_TESTING_GUIDE.md"
if ($check7) { Write-Host "✓" -ForegroundColor Green } else { Write-Host "✗" -ForegroundColor Red }

# Check 8: Implementation summary exists
Write-Host -NoNewLine "Implementation summary... "
$check8 = Test-Path "CHAT_SYSTEM_IMPLEMENTATION_COMPLETE.md"
if ($check8) { Write-Host "✓" -ForegroundColor Green } else { Write-Host "✗" -ForegroundColor Red }

Write-Host ""
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "VERIFICATION COMPLETE" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# Summary
$allChecks = @($check1, $check2, $check3, $check4, $check5, $check6, $check7, $check8)
$passedChecks = ($allChecks | Where-Object { $_ -eq $true } | Measure-Object).Count
$totalChecks = $allChecks.Count

Write-Host "Status: $passedChecks/$totalChecks checks passed" -ForegroundColor Cyan
Write-Host ""

if ($passedChecks -eq $totalChecks) {
    Write-Host "✓ All checks passed! System is ready for testing." -ForegroundColor Green
} else {
    Write-Host "⚠ Some checks failed. Review errors above." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "NEXT STEPS:" -ForegroundColor Cyan
Write-Host "1. Start the Laravel development server"
Write-Host "2. Test creating messages between customer and seller"
Write-Host "3. Verify badge updates in header"
Write-Host "4. Test seller inbox with new split-view layout"
Write-Host "5. Follow: CHAT_SYSTEM_TESTING_GUIDE.md for full testing"
Write-Host ""
