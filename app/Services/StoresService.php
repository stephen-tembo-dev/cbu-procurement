<?php

namespace App\Services;

use App\Contracts\PurchaseOrderRepositoryInterface;
use App\Contracts\StockItemRepositoryInterface;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequisition;
use App\Models\StockItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StoresService
{
    public function __construct(
        private readonly StockItemRepositoryInterface  $stockRepo,
        private readonly PurchaseOrderRepositoryInterface $poRepo,
        private readonly AuditComplianceService        $auditService,
    ) {}

    /**
     * Check whether every item on a PR is available in stock.
     * Returns ['in_stock' => true/false, 'details' => [...per item...]]
     */
    public function checkAvailability(PurchaseRequisition $pr): array
    {
        $pr->loadMissing('items.stockItem');

        $details = $pr->items->map(function ($item) {
            $stock    = $item->stockItem;
            $inStock  = $stock && $stock->is_stocked && $stock->quantity_on_hand >= $item->quantity;

            return [
                'item_id'            => $item->id,
                'description'        => $item->description,
                'requested_quantity' => $item->quantity,
                'available_quantity' => $stock?->quantity_on_hand ?? 0,
                'in_stock'           => $inStock,
            ];
        });

        return [
            'in_stock' => $details->every('in_stock'),
            'details'  => $details->toArray(),
        ];
    }

    /**
     * Issue stocked items from the stores and deduct quantities.
     * Called after Stores marks a PR as 'issued_from_stores'.
     */
    public function issueFromStock(PurchaseRequisition $pr, User $storesOfficer): void
    {
        $pr->loadMissing('items.stockItem');

        DB::transaction(function () use ($pr, $storesOfficer) {
            foreach ($pr->items as $item) {
                if (! $item->stockItem) {
                    throw new RuntimeException("Item '{$item->description}' has no linked stock record.");
                }

                if ($item->stockItem->quantity_on_hand < $item->quantity) {
                    throw new RuntimeException(
                        "Insufficient stock for '{$item->description}'. Available: {$item->stockItem->quantity_on_hand}."
                    );
                }

                $this->stockRepo->adjustQuantity($item->stock_item_id, -$item->quantity);
            }

            $this->auditService->log(
                PurchaseRequisition::class, $pr->id,
                'issued_from_stores',
                [],
                ['issued_by' => $storesOfficer->name, 'issued_at' => now()->toDateTimeString()]
            );
        });
    }

    /**
     * Record that a recurring stock item has been reordered.
     * Updates last_reorder_date to today and computes the next due date.
     */
    public function recordReorder(StockItem $item, User $officer, ?Carbon $reorderDate = null): void
    {
        if (! $item->is_recurring) {
            throw new RuntimeException("Stock item '{$item->stock_code}' is not marked as a recurring item.");
        }

        $date = $reorderDate ?? now();

        $next = $item->reorder_frequency_days
            ? $date->copy()->addDays($item->reorder_frequency_days)
            : null;

        $item->update([
            'last_reorder_date' => $date->toDateString(),
            'next_reorder_date' => $next?->toDateString(),
        ]);

        $this->auditService->log(
            StockItem::class,
            $item->id,
            'reorder_recorded',
            [],
            [
                'recorded_by'      => $officer->name,
                'reorder_date'     => $date->toDateString(),
                'next_reorder_due' => $next?->toDateString(),
            ]
        );
    }

    /**
     * Confirm that procured goods have been received.
     * Updates the PO status to 'delivered' and adjusts stock if items are stocked.
     */
    public function confirmGoodsReceived(PurchaseOrder $po, User $storesOfficer, \Carbon\Carbon $receivedDate): void
    {
        DB::transaction(function () use ($po, $storesOfficer, $receivedDate) {
            $this->poRepo->update($po->id, [
                'status'               => 'delivered',
                'actual_delivery_date' => $receivedDate,
            ]);

            // If any PO items are stocked items, add them back to stock
            $po->loadMissing('items', 'purchaseRequisition.items.stockItem');

            foreach ($po->purchaseRequisition->items as $prItem) {
                if ($prItem->stockItem && $prItem->stockItem->is_stocked) {
                    $this->stockRepo->adjustQuantity($prItem->stock_item_id, $prItem->quantity);
                }
            }

            // Advance PR status
            $po->purchaseRequisition->update(['status' => 'delivered']);

            $this->auditService->log(
                PurchaseOrder::class, $po->id,
                'goods_received',
                ['status' => 'paid'],
                ['status' => 'delivered', 'received_by' => $storesOfficer->name, 'received_at' => $receivedDate->toDateString()]
            );
        });
    }
}
