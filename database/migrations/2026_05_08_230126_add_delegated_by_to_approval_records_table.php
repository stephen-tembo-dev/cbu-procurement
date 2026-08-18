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
        Schema::table('approval_records', function (Blueprint $table) {
            // Non-null means the actor was acting as delegate on behalf of this HOD user
            $table->foreignId('delegated_by')
                ->nullable()
                ->after('actor_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('approval_records', function (Blueprint $table) {
            $table->dropForeign(['delegated_by']);
            $table->dropColumn('delegated_by');
        });
    }
};
