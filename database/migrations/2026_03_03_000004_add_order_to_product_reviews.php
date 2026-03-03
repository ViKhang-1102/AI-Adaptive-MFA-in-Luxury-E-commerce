<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            // add order reference
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('cascade');

            // drop existing unique index and recreate with order_id
            $table->dropUnique(['product_id', 'customer_id']);
            $table->unique(['product_id', 'customer_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropUnique(['product_id', 'customer_id', 'order_id']);
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');
            $table->unique(['product_id', 'customer_id']);
        });
    }
};