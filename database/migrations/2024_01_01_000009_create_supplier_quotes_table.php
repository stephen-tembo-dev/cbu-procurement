<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_requisition_id')
                  ->constrained('purchase_requisitions')
                  ->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->decimal('amount', 15, 2)->nullable()->comment('Null if supplier did not respond');
            $table->date('received_date')->nullable();
            $table->boolean('supplier_contacted')->default(false);
            $table->string('contact_evidence_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_quotes');
    }
};
