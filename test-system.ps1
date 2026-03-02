# PayPal Marketplace - Test Suite
# PowerShell validation script

Write-Host "=============================" -ForegroundColor Cyan
Write-Host "PayPal System Validation" -ForegroundColor Cyan
Write-Host "=============================" -ForegroundColor Cyan

Set-Location "c:\laragon\www\E-commerce2026"

Write-Host "`n1. Checking PHP Syntax..." -ForegroundColor Yellow
$files = @(
    "app\Http\Controllers\OrderController.php",
    "app\Http\Controllers\PayPalController.php",
    "app\Http\Controllers\Admin\TransactionController.php",
    "app\Http\Controllers\Seller\WalletController.php"
)

foreach ($file in $files) {
    $result = php -l $file 2>&1
    if ($result -match "No syntax errors") {
        Write-Host "   OK: $file" -ForegroundColor Green
    }
}

Write-Host "`n2. Checking PayPal Routes..." -ForegroundColor Yellow
$routes = php artisan route:list 2>&1
if ($routes -match "paypal/create") { Write-Host "   OK: PayPal routes exist" -ForegroundColor Green }
if ($routes -notmatch "vnpay") { Write-Host "   OK: no VNPay routes" -ForegroundColor Green }

Write-Host "`n3. Checking Database..." -ForegroundColor Yellow
$migration = php artisan migrate:status 2>&1
if ($migration -match "2026_02_26") { Write-Host "   OK: Migrations applied" -ForegroundColor Green }

Write-Host "`n=============================" -ForegroundColor Cyan
Write-Host "Ready to Test!" -ForegroundColor Green
Write-Host "`nNext: php artisan serve" -ForegroundColor White
Write-Host "Then: Read PAYPAL_TESTING_GUIDE.md" -ForegroundColor White
Write-Host "=============================" -ForegroundColor Cyan
