<?php

use App\Models\CostCentre;
use App\Models\Department;
use App\Models\Procurement\PurchaseRequisition;
use App\Models\Supplier;
use App\Models\User;
use App\Services\ProcurementService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows procurement to correct a quote while the requisition is under procurement review', function () {
    $department = Department::create(['name' => 'Information Technology', 'code' => 'IT']);
    $costCentre = CostCentre::create(['name' => 'IT Operations', 'code' => 'IT-OPS', 'department_id' => $department->id]);
    $requisition = PurchaseRequisition::create([
        'type' => 'product',
        'requester_id' => User::factory()->create(['department_id' => $department->id])->id,
        'department_id' => $department->id,
        'cost_centre_id' => $costCentre->id,
        'status' => 'pending_procurement',
    ]);
    $service = app(ProcurementService::class);
    $quote = $service->addQuote($requisition, ['supplier_id' => Supplier::create(['name' => 'Acme Supplies'])->id, 'amount' => 1200]);

    $updated = $service->updateQuote($quote, ['amount' => 1150, 'notes' => 'Corrected supplier total.']);

    expect((float) $updated->amount)->toBe(1150.0)
        ->and($updated->notes)->toBe('Corrected supplier total.');
});
