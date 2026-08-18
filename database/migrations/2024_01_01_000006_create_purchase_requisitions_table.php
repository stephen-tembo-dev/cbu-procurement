<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique()->comment('System-generated unique reference');
            $table->enum('type', ['product', 'service']);
            $table->foreignId('requester_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->foreignId('cost_centre_id')->constrained('cost_centres')->restrictOnDelete();
            $table->enum('status', [
                'draft',
                'pending_hod',
                'pending_stores',
                'issued_from_stores',
                'pending_bursar',
                'pending_vc_requisition',
                'pending_procurement',
                'pending_audit',
                'pending_vc_payment',
                'pending_payment',
                'paid',
                'delivered',
                'rejected',
                'suspended',
            ])->default('draft');
            $table->boolean('service_consumed_flag')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_requisitions');
    }
};
