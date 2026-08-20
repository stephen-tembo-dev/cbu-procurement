<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_quotes', function (Blueprint $table) {
            $table->unique(
                ['purchase_requisition_id', 'supplier_id'],
                'supplier_quotes_requisition_supplier_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('supplier_quotes', function (Blueprint $table) {
            $table->dropUnique('supplier_quotes_requisition_supplier_unique');
        });
    }
};
