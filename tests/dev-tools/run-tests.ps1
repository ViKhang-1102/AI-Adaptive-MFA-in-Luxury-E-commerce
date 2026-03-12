# PayPal Marketplace - Complete Test Suite (PowerShell)
# Run: ./tests/dev-tools/run-tests.ps1

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "PayPal Marketplace - System Validation" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan

$testDir = "c:\laragon\www\E-commerce2026"
Set-Location $testDir

Write-Host "`n1️⃣ PHP Syntax Validation..." -ForegroundColor Yellow

$controllers = @(
    "app\Http\Controllers\OrderController.php",
    "app\Http\Controllers\PayPalController.php",
    "app\Http\Controllers\Admin\TransactionController.php",
    "app\Http\Controllers\Seller\WalletController.php"
)

foreach ($file in $controllers) {
    $result = php -l $file 2>&1
    if ($result -match "No syntax errors") {
        Write-Host "   ✓ $(Split-Path $file -Leaf)" -ForegroundColor Green
    } else {
        Write-Host "   ✗ $(Split-Path $file -Leaf) - ERROR" -ForegroundColor Red
    }
}

Write-Host "`n2️⃣ Database State Check..." -ForegroundColor Yellow

$output = php artisan tinker --execute="@include 'tests/dev-tools/verify-payment-system.php'" 2>&1
if ($output -match "SYSTEM READY FOR TESTING") {
    Write-Host "   ✓ Database configured correctly" -ForegroundColor Green
} else {
    Write-Host "   ⚠ Database may need setup" -ForegroundColor Yellow
}

Write-Host "`n3️⃣ Route Validation..." -ForegroundColor Yellow

$routes = php artisan route:list 2>&1
if ($routes -match "paypal/create") {
    Write-Host "   ✓ PayPal routes registered" -ForegroundColor Green
} else {
    Write-Host "   ✗ PayPal routes MISSING" -ForegroundColor Red
}

if ($routes -match "vnpay") {
    Write-Host "   ✗ VNPay routes still exist!" -ForegroundColor Red
} else {
    Write-Host "   ✓ VNPay routes removed" -ForegroundColor Green
}

Write-Host "`n4️⃣ Environment Configuration..." -ForegroundColor Yellow

$envFile = ".env"
if (Test-Path $envFile) {
    $envContent = Get-Content $envFile
    if ($envContent -match "PAYPAL_SANDBOX_CLIENT_ID") {
        Write-Host "   ✓ PayPal credentials configured" -ForegroundColor Green
    } else {
        Write-Host "   ✗ PayPal credentials missing" -ForegroundColor Red
    }
    
    if ($envContent -match "PAYPAL_MODE=sandbox") {
        Write-Host "   ✓ PayPal mode: Sandbox" -ForegroundColor Green
    }
} else {
    Write-Host "   ⚠ .env file not found" -ForegroundColor Yellow
}

Write-Host "`n5️⃣ File Structure Check..." -ForegroundColor Yellow

$requiredFiles = @(
    "app\Http\Controllers\PayPalController.php",
    "app\Http\Controllers\Admin\TransactionController.php",
    "app\Http\Controllers\Seller\WalletController.php",
    "resources\views\checkout\index.blade.php",
    "tests\dev-tools\verify-payment-system.php",
    "setup-payment.php"
)

foreach ($file in $requiredFiles) {
    if (Test-Path $file) {
        Write-Host "   ✓ $(Split-Path $file -Leaf)" -ForegroundColor Green
    } else {
        Write-Host "   ✗ $(Split-Path $file -Leaf) - MISSING" -ForegroundColor Red
    }
}

Write-Host "`n==========================================" -ForegroundColor Cyan
Write-Host "✅ VALIDATION COMPLETE!" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Cyan

Write-Host "`n🚀 NEXT STEPS:" -ForegroundColor Yellow
Write-Host ""
Write-Host "1️⃣  Terminal 1 - Start Laravel Server:" -ForegroundColor White
Write-Host "    cd c:\laragon\www\E-commerce2026" -ForegroundColor Gray
Write-Host "    php artisan serve" -ForegroundColor Gray
Write-Host ""
Write-Host "2️⃣  Terminal 2 - Setup Payment System:" -ForegroundColor White
Write-Host "    php artisan tinker" -ForegroundColor Gray
Write-Host "    [at] include 'setup-payment.php'" -ForegroundColor Gray
Write-Host "    [at] exit" -ForegroundColor Gray
Write-Host ""
Write-Host "3️⃣  Browser - Test Payment Flow:" -ForegroundColor White
Write-Host "    http://localhost:8000" -ForegroundColor Gray
Write-Host "    - Login as admin@example.com" -ForegroundColor Gray
Write-Host "    - Go to /admin/fees" -ForegroundColor Gray
Write-Host "    - Set commission to 10%" -ForegroundColor Gray
Write-Host ""
Write-Host "4️⃣  Customer Payment:" -ForegroundColor White
Write-Host "    - Login as customer-test@example.com" -ForegroundColor Gray
Write-Host "    - Add product to cart" -ForegroundColor Gray
Write-Host "    - Checkout: Select PayPal, Place Order" -ForegroundColor Gray
Write-Host ""
Write-Host "5️⃣  Admin Approval:" -ForegroundColor White
Write-Host "    - Go to /admin/wallet" -ForegroundColor Gray
Write-Host "    - Click Approve button" -ForegroundColor Gray
Write-Host "    - PayPal payout processed" -ForegroundColor Gray
Write-Host ""
Write-Host "📖 Read the complete guide:" -ForegroundColor Yellow
Write-Host "    PAYPAL_TESTING_GUIDE.md" -ForegroundColor Gray
Write-Host "    QUICK_REFERENCE.md" -ForegroundColor Gray
Write-Host ""

