<?php

use App\Models\CostCentre;
use App\Models\Department;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequisition;
use App\Models\Procurement\PurchaseRequisitionItem;
use App\Models\StockItem;
use App\Models\Supplier;
use App\Models\User;
use App\Services\StoresService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('adds each received quantity to linked stock without double counting earlier receipts', function () {
    $department = Department::create(['name' => 'Stores', 'code' => 'STORES']);
    $costCentre = CostCentre::create(['name' => 'Stores', 'code' => 'STORES-01', 'department_id' => $department->id]);
    $requester = User::factory()->create(['department_id' => $department->id]);
    $storesOfficer = User::factory()->create();
    $requisition = PurchaseRequisition::create([
        'type' => 'product',
        'requester_id' => $requester->id,
        'department_id' => $department->id,
        'cost_centre_id' => $costCentre->id,
        'status' => 'paid',
    ]);
    $stock = StockItem::create([
        'stock_code' => 'PAPER-A4',
        'description' => 'A4 Paper',
        'quantity_on_hand' => 10,
        'reorder_level' => 2,
        'is_stocked' => true,
        'is_active' => true,
    ]);
    $prItem = PurchaseRequisitionItem::create([
        'purchase_requisition_id' => $requisition->id,
        'stock_item_id' => $stock->id,
        'description' => 'A4 Paper',
        'quantity' => 5,
        'unit_price_estimated' => 100,
        'total_price_estimated' => 500,
    ]);
    $po = PurchaseOrder::create([
        'purchase_requisition_id' => $requisition->id,
        'supplier_id' => Supplier::create(['name' => 'Office Supply Co.'])->id,
        'total_value' => 500,
        'status' => 'issued',
    ]);
    $poItem = $po->items()->create([
        'purchase_requisition_item_id' => $prItem->id,
        'description' => 'A4 Paper',
        'quantity' => 5,
        'unit_price' => 100,
        'total_price' => 500,
    ]);

    app(StoresService::class)->recordGoodsReceipt($po, [$poItem->id => 2], now(), $storesOfficer);
    expect((float) $stock->fresh()->quantity_on_hand)->toBe(12.0)
        ->and($po->fresh()->status)->toBe('partially_delivered');

    app(StoresService::class)->recordGoodsReceipt($po->fresh(), [$poItem->id => 3], now(), $storesOfficer);
    expect((float) $stock->fresh()->quantity_on_hand)->toBe(15.0)
        ->and($po->fresh()->status)->toBe('delivered')
        ->and($requisition->fresh()->status)->toBe('delivered');
});
