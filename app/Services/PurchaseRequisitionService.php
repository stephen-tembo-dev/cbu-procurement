<?php

namespace App\Services;

use App\Contracts\PurchaseRequisitionItemRepositoryInterface;
use App\Contracts\PurchaseRequisitionRepositoryInterface;
use App\Models\Procurement\PurchaseRequisition;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PurchaseRequisitionService
{
    public function __construct(
        private readonly PurchaseRequisitionRepositoryInterface     $prRepo,
        private readonly PurchaseRequisitionItemRepositoryInterface $itemRepo,
        private readonly AuditComplianceService                     $auditService,
    ) {}

    /**
     * Create a new PR in 'draft' status and immediately submit it to the HOD.
     * $data keys: type, cost_centre_id, notes, items[]
     * Each item: description, stock_item_id?, quantity, unit_of_measure?, unit_price_estimated, category?
     */
    public function createAndSubmit(array $data, User $requester): PurchaseRequisition
    {
        return DB::transaction(function () use ($data, $requester) {
            $pr = $this->prRepo->create([
                'type'          => $data['type'],
                'requester_id'  => $requester->id,
                'department_id' => $requester->department_id,
                'cost_centre_id'=> $data['cost_centre_id'],
                'status'        => 'pending_hod',
                'notes'         => $data['notes'] ?? null,
                'service_consumed_flag' => $this->detectServiceConsumed($data),
            ]);

            $this->itemRepo->syncItems($pr->id, $data['items']);

            $this->auditService->log(PurchaseRequisition::class, $pr->id, 'created', [], [
                'reference_no' => $pr->reference_no,
                'type'         => $pr->type,
                'requester'    => $requester->name,
            ]);

            return $pr->fresh(['items', 'requester', 'department', 'costCentre']);
        });
    }

    /**
     * Update a PR that is still in 'draft' or 'pending_hod' status.
     * Once past HOD it is locked.
     */
    public function update(int $prId, array $data, User $actor): PurchaseRequisition
    {
        $pr = $this->prRepo->findById($prId);

        if (! in_array($pr->status, ['draft', 'pending_hod'], true)) {
            throw new RuntimeException('A PR can only be edited while in draft or pending HOD review.');
        }

        return DB::transaction(function () use ($pr, $data, $actor) {
            $old = $pr->only(['notes', 'cost_centre_id']);

            $this->prRepo->update($pr->id, array_filter([
                'cost_centre_id' => $data['cost_centre_id'] ?? null,
                'notes'          => $data['notes'] ?? null,
            ]));

            if (isset($data['items'])) {
                $this->itemRepo->syncItems($pr->id, $data['items']);
            }

            $this->auditService->log(PurchaseRequisition::class, $pr->id, 'updated', $old, $data);

            return $this->prRepo->findWithFullTrail($pr->id);
        });
    }

    /**
     * Load the full audit trail summary for the VC or admin view.
     */
    public function getSummary(int $prId): PurchaseRequisition
    {
        $pr = $this->prRepo->findWithFullTrail($prId);

        if (! $pr) {
            throw new RuntimeException("Purchase Requisition #{$prId} not found.");
        }

        return $pr;
    }

    /**
     * Detect whether a service PR was raised after consumption.
     * Looks for a 'service_date' in the items that is in the past relative to today.
     */
    private function detectServiceConsumed(array $data): bool
    {
        if ($data['type'] !== 'service') {
            return false;
        }

        $serviceDate = $data['service_date'] ?? null;

        return $serviceDate && now()->startOfDay()->gt($serviceDate);
    }
}
