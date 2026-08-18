<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_requisition_id')
                  ->constrained('purchase_requisitions')
                  ->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->string('stage')->comment('hod | stores | bursar | vc_requisition | audit | vc_payment');
            $table->enum('decision', ['approved', 'rejected', 'committed', 'not_committed', 'issued', 'suspended', 'noted']);
            $table->text('comment')->nullable();
            $table->timestamp('acted_at')->useCurrent();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_records');
    }
};
