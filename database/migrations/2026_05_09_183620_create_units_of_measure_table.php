<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units_of_measure', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // e.g. "Pieces"
            $table->string('abbreviation');   // e.g. "pcs"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('units_of_measure')->insert([
            ['name' => 'Pieces',    'abbreviation' => 'pcs',  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Boxes',     'abbreviation' => 'box',  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Reams',     'abbreviation' => 'ream', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cartons',   'abbreviation' => 'ctn',  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sets',      'abbreviation' => 'set',  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pairs',     'abbreviation' => 'pair', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kilograms', 'abbreviation' => 'kg',   'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Grams',     'abbreviation' => 'g',    'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Litres',    'abbreviation' => 'L',    'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Millilitres','abbreviation'=> 'mL',   'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Metres',    'abbreviation' => 'm',    'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rolls',     'abbreviation' => 'roll', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Hours',     'abbreviation' => 'hrs',  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Days',      'abbreviation' => 'days', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Months',    'abbreviation' => 'mths', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Each',      'abbreviation' => 'ea',   'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pallets',   'abbreviation' => 'plt',  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Packets',   'abbreviation' => 'pkt',  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('units_of_measure');
    }
};
