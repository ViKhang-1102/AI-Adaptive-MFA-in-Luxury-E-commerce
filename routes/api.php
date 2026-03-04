<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Api\WalletController;

// fake shipment webhook used by Postman or external services
Route::post('shipper/update-status', [OrderController::class, 'shipperUpdateStatus']);
// API to simulate payout/withdrawal: deduct from system (admin) wallet and mark payout success
Route::post('withdraw', [WalletController::class, 'withdraw']);
