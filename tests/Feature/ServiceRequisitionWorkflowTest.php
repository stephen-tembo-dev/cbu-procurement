<?php

use App\Models\CostCentre;
use App\Models\Department;
use App\Models\Procurement\PurchaseRequisition;
use App\Models\User;
use App\Services\WorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('sends an approved service requisition from HOD directly to the Bursar', function () {
    $role = Role::create(['name' => 'hod']);
    $hod = User::factory()->create();
    $hod->assignRole($role);

    $department = Department::create([
        'name' => 'Information Technology',
        'code' => 'IT',
        'hod_user_id' => $hod->id,
    ]);
    $costCentre = CostCentre::create([
        'name' => 'IT Operations',
        'code' => 'IT-OPS',
        'department_id' => $department->id,
    ]);
    $requester = User::factory()->create(['department_id' => $department->id]);
    $requisition = PurchaseRequisition::create([
        'type' => 'service',
        'requester_id' => $requester->id,
        'department_id' => $department->id,
        'cost_centre_id' => $costCentre->id,
        'status' => 'pending_hod',
    ]);

    app(WorkflowService::class)->processHodDecision($requisition, $hod, 'approved');

    expect($requisition->fresh()->status)->toBe('pending_bursar');
});
