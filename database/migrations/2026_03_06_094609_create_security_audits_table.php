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
        Schema::create('security_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action'); // e.g., 'checkout'
            $table->decimal('amount', 10, 2)->nullable();
            $table->float('risk_score')->default(0);
            $table->string('level'); // 'low', 'medium', 'high'
            $table->string('suggestion'); // 'allow', 'mfa', 'block'
            $table->string('result')->nullable(); // 'success', 'failed', 'blocked', 'pending'
            $table->json('metadata')->nullable(); // Detailed reasons or inputs sent
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_audits');
    }
};
