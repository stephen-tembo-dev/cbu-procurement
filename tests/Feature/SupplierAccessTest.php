<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('allows a procurement user with supplier management permission to access suppliers', function () {
    $permission = Permission::create(['name' => 'suppliers.manage']);
    $role = Role::create(['name' => 'procurement']);
    $role->givePermissionTo($permission);

    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user)
        ->get(route('admin.suppliers'))
        ->assertOk();
});
