#!/bin/bash
# PayPal Marketplace - Complete Test Suite
# Save as: tests/dev-tools/run-tests.sh or run manually in PowerShell

echo "=========================================="
echo "PayPal Marketplace - System Validation"
echo "=========================================="

cd c:\laragon\www\E-commerce2026

echo ""
echo "1️⃣ PHP Syntax Validation..."
php -l app/Http/Controllers/OrderController.php > /dev/null && echo "   ✓ OrderController" || echo "   ✗ OrderController ERROR"
php -l app/Http/Controllers/PayPalController.php > /dev/null && echo "   ✓ PayPalController" || echo "   ✗ PayPalController ERROR"
php -l app/Http/Controllers/Admin/TransactionController.php > /dev/null && echo "   ✓ TransactionController" || echo "   ✗ TransactionController ERROR"
php -l app/Http/Controllers/Seller/WalletController.php > /dev/null && echo "   ✓ WalletController" || echo "   ✗ WalletController ERROR"

echo ""
echo "2️⃣ Database Integrity Check..."
php artisan migrate:status | grep "2026_02_26" > /dev/null && echo "   ✓ Migrations applied" || echo "   ✗ Migrations NOT applied"

echo ""
echo "3️⃣ Route Validation..."
php artisan route:list | grep "paypal/create" > /dev/null && echo "   ✓ PayPal routes registered" || echo "   ✗ PayPal routes MISSING"
php artisan route:list | grep "vnpay" > /dev/null && echo "   ✗ VNPay routes still exist" || echo "   ✓ VNPay routes removed"

echo ""
echo "4️⃣ Configuration Check..."
# This would need PHP to parse env
echo "   ℹ Verify .env has PayPal credentials:"
echo "      - PAYPAL_SANDBOX_CLIENT_ID"
echo "      - PAYPAL_SANDBOX_SECRET"
echo "      - PAYPAL_MODE=sandbox"

echo ""
echo "5️⃣ Payment System Verification..."
php tests/dev-tools/verify-payment-system.php

echo ""
echo "✅ Test run complete."

