<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the framework
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

$pending = WalletTransaction::where('status', 'pending')->get();
echo "Total pending transactions: " . $pending->count() . "\n";
foreach ($pending as $tx) {
    $seller = $tx->wallet?->user;
    echo "TX #{$tx->id}: seller_id=" . ($seller?->id ?? 'null') . " amount={$tx->amount} type={$tx->type}\n";
    if ($seller) {
        echo "  seller wallet balance= " . $seller->wallet->balance . "\n";
        $computed = $seller->wallet->transactions()->where('status','completed')->sum(DB::raw('CASE WHEN type = \"credit\" THEN amount ELSE -amount END'));
        echo "  computed completed= {$computed}\n";
    }
}
