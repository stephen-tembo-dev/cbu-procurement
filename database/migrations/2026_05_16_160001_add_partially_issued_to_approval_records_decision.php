<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE approval_records MODIFY COLUMN decision ENUM('approved','rejected','committed','not_committed','issued','partially_issued','suspended','noted','returned_for_revision','resubmitted') NOT NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE approval_records MODIFY COLUMN decision ENUM('approved','rejected','committed','not_committed','issued','suspended','noted','returned_for_revision','resubmitted') NOT NULL");
    }
};
