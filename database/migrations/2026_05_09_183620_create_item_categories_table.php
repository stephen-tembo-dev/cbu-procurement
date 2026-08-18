<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('item_categories')->insert([
            ['name' => 'Stationery & Office Supplies',       'description' => 'Pens, paper, files, toner, etc.',             'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'IT & Electronics',                   'description' => 'Computers, accessories, peripherals.',         'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Furniture & Fittings',               'description' => 'Desks, chairs, shelving, fixtures.',           'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Laboratory Equipment',               'description' => 'Lab instruments, chemicals, glassware.',       'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cleaning & Maintenance',             'description' => 'Detergents, mops, maintenance supplies.',      'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Medical & Health Supplies',          'description' => 'First aid, PPE, medical consumables.',         'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Books & Publications',               'description' => 'Textbooks, journals, reference materials.',    'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Uniforms & Clothing',                'description' => 'Staff uniforms, protective clothing.',         'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Food & Catering',                    'description' => 'Catering supplies, refreshments.',             'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Transport & Fuel',                   'description' => 'Vehicle fuel, transport costs.',               'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Consultancy & Professional Services','description' => 'Contracted services, professional fees.',      'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Construction & Civil Works',         'description' => 'Building materials, civil works.',             'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Printing & Branding',                'description' => 'Printed materials, banners, branding.',        'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Other',                              'description' => 'Items not covered by other categories.',       'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('item_categories');
    }
};
