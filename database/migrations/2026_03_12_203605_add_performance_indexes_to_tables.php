<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index('created_at');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->index('created_at');
        });

        Schema::table('security_audits', function (Blueprint $table) {
            $table->index('suggestion');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        Schema::table('security_audits', function (Blueprint $table) {
            $table->dropIndex(['suggestion']);
            $table->dropIndex(['created_at']);
        });
    }
};
