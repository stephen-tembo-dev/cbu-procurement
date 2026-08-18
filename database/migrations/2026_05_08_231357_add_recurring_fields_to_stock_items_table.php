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
        Schema::table('stock_items', function (Blueprint $table) {
            $table->boolean('is_recurring')->default(false)->after('reorder_level');
            // Number of days between reorders (30 = monthly, 91 = quarterly, etc.)
            $table->unsignedSmallInteger('reorder_frequency_days')->nullable()->after('is_recurring');
            $table->date('last_reorder_date')->nullable()->after('reorder_frequency_days');
            $table->date('next_reorder_date')->nullable()->after('last_reorder_date');

            $table->index(['is_recurring', 'next_reorder_date']);
        });
    }

    public function down(): void
    {
        Schema::table('stock_items', function (Blueprint $table) {
            $table->dropIndex(['is_recurring', 'next_reorder_date']);
            $table->dropColumn(['is_recurring', 'reorder_frequency_days', 'last_reorder_date', 'next_reorder_date']);
        });
    }
};
