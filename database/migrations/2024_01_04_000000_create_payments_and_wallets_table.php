<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('restrict');
            $table->string('payment_method');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded'])->default('pending');
            $table->decimal('amount', 12, 2);
            $table->string('transaction_id')->nullable()->unique();
            $table->string('reference_code')->nullable();
            $table->text('response_data')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->index('order_id');
        });

        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image');
            $table->string('link')->nullable();
            $table->enum('banner_type', ['main', 'promo', 'category'])->default('main');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamps();
            $table->index('is_active');
        });

        Schema::create('system_fees', function (Blueprint $table) {
            $table->id();
            $table->decimal('platform_fee_percent', 5, 2)->default(5);
            $table->decimal('transaction_fee_percent', 5, 2)->default(2);
            $table->decimal('shipping_fee_default', 10, 2)->default(20000);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('e_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('balance', 14, 2)->default(0);
            $table->decimal('total_received', 14, 2)->default(0);
            $table->decimal('total_spent', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('e_wallets')->onDelete('cascade');
            $table->enum('type', ['credit', 'debit'])->default('credit');
            $table->decimal('amount', 14, 2);
            $table->string('description');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps();
            $table->index('wallet_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('e_wallets');
        Schema::dropIfExists('system_fees');
        Schema::dropIfExists('banners');
        Schema::dropIfExists('payments');
    }
};
