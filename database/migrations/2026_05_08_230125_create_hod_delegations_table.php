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
        Schema::create('hod_delegations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('hod_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('delegate_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('reason')->nullable();
            $table->date('starts_at');
            $table->date('ends_at');
            $table->boolean('is_active')->default(true);
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['department_id', 'is_active']);
            $table->index(['delegate_user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hod_delegations');
    }
};
