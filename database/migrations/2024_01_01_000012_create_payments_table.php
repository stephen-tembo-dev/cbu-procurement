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
            $table->foreignId('purchase_order_id')
                  ->constrained('purchase_orders')
                  ->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'paid', 'suspended'])->default('pending');
            $table->foreignId('vc_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('vc_approved_at')->nullable();
            $table->foreignId('bursar_confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('bursar_confirmed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('delay_comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
