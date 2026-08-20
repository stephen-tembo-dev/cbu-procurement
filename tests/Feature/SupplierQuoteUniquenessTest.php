<?php

use App\Models\CostCentre;
use App\Models\Department;
use App\Models\Procurement\PurchaseRequisition;
use App\Models\Supplier;
use App\Models\User;
use App\Services\ProcurementService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('rejects a second quote from the same supplier for one requisition', function () {
    $requester = User::factory()->create();
    $department = Department::create(['name' => 'Information Technology', 'code' => 'IT']);
    $costCentre = CostCentre::create([
        'name' => 'IT Operations',
        'code' => 'IT-OPS',
        'department_id' => $department->id,
    ]);
    $requisition = PurchaseRequisition::create([
        'type' => 'product',
        'requester_id' => $requester->id,
        'department_id' => $department->id,
        'cost_centre_id' => $costCentre->id,
        'status' => 'pending_procurement',
    ]);
    $supplier = Supplier::create(['name' => 'Acme Supplies']);

    $service = app(ProcurementService::class);
    $service->addQuote($requisition, ['supplier_id' => $supplier->id, 'amount' => 1200]);

    expect(fn () => $service->addQuote($requisition, ['supplier_id' => $supplier->id, 'amount' => 1300]))
        ->toThrow(RuntimeException::class, 'This supplier already has a quote for this requisition.');
});
