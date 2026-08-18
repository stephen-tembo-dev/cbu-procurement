<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('purchase_requisition_id')
                  ->constrained('purchase_requisitions')
                  ->restrictOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->decimal('total_value', 15, 2)->default(0);
            $table->enum('status', [
                'draft',
                'issued',
                'partially_delivered',
                'delivered',
                'cancelled',
            ])->default('draft');
            $table->date('issued_date')->nullable();
            $table->date('expected_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
