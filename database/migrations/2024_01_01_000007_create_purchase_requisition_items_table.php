<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_requisition_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_requisition_id')
                  ->constrained('purchase_requisitions')
                  ->cascadeOnDelete();
            $table->foreignId('stock_item_id')->nullable()->constrained('stock_items')->nullOnDelete();
            $table->string('description');
            $table->string('category')->nullable();
            $table->decimal('quantity', 12, 2);
            $table->string('unit_of_measure')->nullable();
            $table->decimal('unit_price_estimated', 15, 2)->default(0);
            $table->decimal('total_price_estimated', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_requisition_items');
    }
};
