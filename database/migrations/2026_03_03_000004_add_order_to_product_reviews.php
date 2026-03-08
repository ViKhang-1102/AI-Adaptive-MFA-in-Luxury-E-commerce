<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // If the order_id column already exists, assume the migration has already been applied
        // (or the schema already contains the intended layout). This prevents failures
        // when the migrations are run against a database that already matches the target.
        if (Schema::hasColumn('product_reviews', 'order_id')) {
            return;
        }

        Schema::table('product_reviews', function (Blueprint $table) {
            // Add order reference
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('cascade');

            // Drop the existing unique index (if it exists) and recreate with order_id
            try {
                $table->dropUnique(['product_id', 'customer_id']);
            } catch (\Exception $e) {
                // ignore if it does not exist or is in use by a constraint
            }

            $table->unique(['product_id', 'customer_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            // Restore original uniqueness if needed
            try {
                $table->dropUnique(['product_id', 'customer_id', 'order_id']);
            } catch (\Exception $e) {
                // ignore if missing
            }

            if (Schema::hasColumn('product_reviews', 'order_id')) {
                try {
                    $table->dropForeign(['order_id']);
                } catch (\Exception $e) {
                    // ignore if no foreign key
                }
                $table->dropColumn('order_id');
            }

            // Ensure original unique exists
            if (Schema::hasColumn('product_reviews', 'product_id') && Schema::hasColumn('product_reviews', 'customer_id')) {
                try {
                    $table->unique(['product_id', 'customer_id']);
                } catch (\Exception $e) {
                    // ignore if already exists
                }
            }
        });
    }
};