<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Administration',    'code' => 'ADMIN'],
            ['name' => 'Finance',           'code' => 'FIN'],
            ['name' => 'Information Technology', 'code' => 'IT'],
            ['name' => 'Procurement',       'code' => 'PROC'],
            ['name' => 'Academic Affairs',  'code' => 'ACAD'],
            ['name' => 'Human Resources',   'code' => 'HR'],
            ['name' => 'Stores',            'code' => 'STORES'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['code' => $dept['code']], $dept);
        }
    }
}
