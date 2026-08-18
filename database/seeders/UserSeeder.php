<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $dept = fn (string $code) => Department::where('code', $code)->value('id');

        $users = [
            [
                'name'          => 'System Admin',
                'email'         => 'admin@binarypivot.test',
                'department_id' => $dept('ADMIN'),
                'role'          => 'admin',
            ],
            [
                'name'          => 'Alice Requester',
                'email'         => 'requester@binarypivot.test',
                'department_id' => $dept('IT'),
                'role'          => 'requester',
            ],
            [
                'name'          => 'Bob HOD',
                'email'         => 'hod@binarypivot.test',
                'department_id' => $dept('IT'),
                'role'          => 'hod',
            ],
            [
                'name'          => 'Carol Stores',
                'email'         => 'stores@binarypivot.test',
                'department_id' => $dept('STORES'),
                'role'          => 'stores',
            ],
            [
                'name'          => 'David Bursar',
                'email'         => 'bursar@binarypivot.test',
                'department_id' => $dept('FIN'),
                'role'          => 'bursar',
            ],
            [
                'name'          => 'Eve VC',
                'email'         => 'vc@binarypivot.test',
                'department_id' => $dept('ADMIN'),
                'role'          => 'vc',
            ],
            [
                'name'          => 'Frank Procurement',
                'email'         => 'procurement@binarypivot.test',
                'department_id' => $dept('PROC'),
                'role'          => 'procurement',
            ],
            [
                'name'          => 'Grace Auditor',
                'email'         => 'auditor@binarypivot.test',
                'department_id' => $dept('FIN'),
                'role'          => 'auditor',
            ],
        ];

        foreach ($users as $data) {
            $role = $data['role'];

            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'              => $data['name'],
                    'password'          => Hash::make('password'),
                    'department_id'     => $data['department_id'],
                    'email_verified_at' => now(),
                    'is_active'         => true,
                ]
            );

            $user->syncRoles([$role]);
        }

        // Set the IT department's HOD
        $hodUser = User::where('email', 'hod@binarypivot.test')->first();
        Department::where('code', 'IT')->update(['hod_user_id' => $hodUser?->id]);
    }
}
