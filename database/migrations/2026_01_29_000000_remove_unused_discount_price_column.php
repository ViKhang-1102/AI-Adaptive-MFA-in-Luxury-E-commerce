<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The discount_price column was never used by the application.
     * The system uses discount_percent + dynamic calculation via Product::getDiscountedPrice()
     * instead of storing a separate discount_price column.
     * This migration removes the unused column to clean up the schema.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('discount_price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('discount_price', 12, 2)->nullable()->after('price');
        });
    }
};
