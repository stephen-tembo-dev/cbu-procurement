<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cost_centre_id')->constrained('cost_centres')->cascadeOnDelete();
            $table->unsignedSmallInteger('fiscal_year');
            $table->decimal('allocated_amount', 15, 2)->default(0);
            $table->decimal('committed_amount', 15, 2)->default(0)->comment('Amounts Bursar has committed but not yet paid');
            $table->decimal('spent_amount', 15, 2)->default(0)->comment('Amounts actually paid');
            $table->timestamps();
            $table->unique(['cost_centre_id', 'fiscal_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_allocations');
    }
};
