<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SecurityAudit;

$audits = SecurityAudit::orderBy('id', 'desc')->take(10)->get();

echo "ID | User | Action | Amount | Score | Suggestion | Result\n";
echo "---------------------------------------------------------\n";
foreach ($audits as $a) {
    echo "{$a->id} | {$a->user_id} | {$a->action} | {$a->amount} | {$a->risk_score} | {$a->suggestion} | {$a->result}\n";
}
