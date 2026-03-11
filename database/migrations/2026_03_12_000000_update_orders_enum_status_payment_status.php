<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Extend enum values for status and payment_status without removing existing ones.
        // This migration is safe for MySQL and SQLite (uses raw SQL for enum altering).
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `orders` MODIFY `status` ENUM('pending','review','paid','processing','shipped','delivered','cancelled','confirmed','returned') NOT NULL DEFAULT 'pending'");
            DB::statement("ALTER TABLE `orders` MODIFY `payment_status` ENUM('unpaid','pending','paid','failed','refunded') NOT NULL DEFAULT 'unpaid'");
        } elseif ($driver === 'sqlite') {
            // SQLite does not enforce enum: no action required.
        } else {
            // For other DBs, fallback to no-op.
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `orders` MODIFY `status` ENUM('pending','confirmed','processing','shipped','delivered','cancelled','returned') NOT NULL DEFAULT 'pending'");
            DB::statement("ALTER TABLE `orders` MODIFY `payment_status` ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending'");
        }
    }
};
