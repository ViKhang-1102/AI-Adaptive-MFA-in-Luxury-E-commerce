<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->string('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['customer_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_notifications');
    }
};