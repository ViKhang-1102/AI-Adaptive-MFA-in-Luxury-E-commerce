<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rename system_fees to a backup, then recreate with new structure
        if (Schema::hasTable('system_fees')) {
            Schema::table('system_fees', function (Blueprint $table) {
                // Add new columns if they don't exist
                if (!Schema::hasColumn('system_fees', 'name')) {
                    $table->string('name')->nullable()->after('id');
                }
                if (!Schema::hasColumn('system_fees', 'fee_type')) {
                    $table->enum('fee_type', ['percentage', 'fixed'])->default('percentage')->after('name');
                }
                if (!Schema::hasColumn('system_fees', 'fee_value')) {
                    $table->decimal('fee_value', 10, 2)->nullable()->after('fee_type');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('system_fees', function (Blueprint $table) {
            if (Schema::hasColumn('system_fees', 'name')) {
                $table->dropColumn('name');
            }
            if (Schema::hasColumn('system_fees', 'fee_type')) {
                $table->dropColumn('fee_type');
            }
            if (Schema::hasColumn('system_fees', 'fee_value')) {
                $table->dropColumn('fee_value');
            }
        });
    }
};
