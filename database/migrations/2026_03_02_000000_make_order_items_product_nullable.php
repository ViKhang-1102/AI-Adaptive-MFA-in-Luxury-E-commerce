<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // drop the existing foreign key constraint (restrict)
            $table->dropForeign(['product_id']);

            // make product_id nullable and change constraint to set null on delete
            $table->unsignedBigInteger('product_id')->nullable()->change();
            $table->foreign('product_id')
                  ->references('id')->on('products')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // drop new FK
            $table->dropForeign(['product_id']);

            // make column non-nullable again
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
            $table->foreign('product_id')
                  ->references('id')->on('products')
                  ->onDelete('restrict');
        });
    }
};