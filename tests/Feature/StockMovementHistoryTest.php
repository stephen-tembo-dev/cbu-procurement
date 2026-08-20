<?php

use App\Models\CostCentre;
use App\Models\Department;
use App\Models\Procurement\PurchaseRequisition;
use App\Models\Procurement\PurchaseRequisitionItem;
use App\Models\StockItem;
use App\Models\User;
use App\Services\StoresService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('records the Stores officer, quantity and resulting balance when stock is issued', function () {
    $department = Department::create(['name' => 'Library', 'code' => 'LIB']);
    $costCentre = CostCentre::create(['name' => 'Library', 'code' => 'LIB-01', 'department_id' => $department->id]);
    $requester = User::factory()->create(['department_id' => $department->id]);
    $officer = User::factory()->create(['name' => 'Stores Officer']);
    $stock = StockItem::create([
        'stock_code' => 'PAPER-A4', 'description' => 'A4 Paper', 'quantity_on_hand' => 20,
        'reorder_level' => 2, 'is_stocked' => true, 'is_active' => true,
    ]);
    $requisition = PurchaseRequisition::create([
        'type' => 'product', 'requester_id' => $requester->id, 'department_id' => $department->id,
        'cost_centre_id' => $costCentre->id, 'status' => 'pending_stores',
    ]);
    PurchaseRequisitionItem::create([
        'purchase_requisition_id' => $requisition->id, 'stock_item_id' => $stock->id,
        'description' => 'A4 Paper', 'quantity' => 4, 'unit_price_estimated' => 100, 'total_price_estimated' => 400,
    ]);

    app(StoresService::class)->issueFromStock($requisition, $officer);

    $movement = $stock->fresh()->movements()->sole();
    expect($movement->movement_type)->toBe('issued')
        ->and((float) $movement->quantity_out)->toBe(4.0)
        ->and((float) $movement->balance_after)->toBe(16.0)
        ->and($movement->performedBy->is($officer))->toBeTrue()
        ->and($movement->purchase_requisition_id)->toBe($requisition->id);
});
