<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE approval_records MODIFY COLUMN decision ENUM('approved','rejected','committed','not_committed','issued','suspended','noted','returned_for_revision','resubmitted') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE approval_records MODIFY COLUMN decision ENUM('approved','rejected','committed','not_committed','issued','suspended','noted','returned_for_revision') NOT NULL");
    }
};
