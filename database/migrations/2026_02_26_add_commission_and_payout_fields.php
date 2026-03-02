<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_fees', function (Blueprint $table) {
            if (!Schema::hasColumn('system_fees', 'is_platform_commission')) {
                $table->boolean('is_platform_commission')->default(false)->after('description');
            }
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('wallet_transactions', 'payout_approved_at')) {
                $table->timestamp('payout_approved_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('wallet_transactions', 'payout_rejected_at')) {
                $table->timestamp('payout_rejected_at')->nullable()->after('payout_approved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('system_fees', function (Blueprint $table) {
            if (Schema::hasColumn('system_fees', 'is_platform_commission')) {
                $table->dropColumn('is_platform_commission');
            }
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('wallet_transactions', 'payout_approved_at')) {
                $table->dropColumn('payout_approved_at');
            }
            if (Schema::hasColumn('wallet_transactions', 'payout_rejected_at')) {
                $table->dropColumn('payout_rejected_at');
            }
        });
    }
};
