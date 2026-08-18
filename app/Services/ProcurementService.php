<?php

namespace App\Services;

use App\Contracts\PurchaseOrderRepositoryInterface;
use App\Contracts\SupplierQuoteRepositoryInterface;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequisition;
use App\Models\Procurement\SupplierQuote;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProcurementService
{
    public function __construct(
        private readonly SupplierQuoteRepositoryInterface $quoteRepo,
        private readonly PurchaseOrderRepositoryInterface $poRepo,
        private readonly AuditComplianceService           $auditService,
    ) {}

    /**
     * Add a supplier quote (with or without a responded amount) to a PR.
     */
    public function addQuote(PurchaseRequisition $pr, array $data): SupplierQuote
    {
        if ($pr->status !== 'pending_procurement') {
            throw new RuntimeException('Quotes can only be added when the PR is in procurement review.');
        }

        $quote = $this->quoteRepo->create([
            'purchase_requisition_id' => $pr->id,
            'supplier_id'             => $data['supplier_id'],
            'amount'                  => $data['amount'] ?? null,
            'received_date'           => $data['received_date'] ?? null,
            'supplier_contacted'      => $data['supplier_contacted'] ?? true,
            'contact_evidence_path'   => $data['contact_evidence_path'] ?? null,
            'notes'                   => $data['notes'] ?? null,
        ]);

        $this->auditService->log(PurchaseRequisition::class, $pr->id, 'quote_added', [], [
            'supplier_id' => $data['supplier_id'],
            'amount'      => $data['amount'] ?? 'no response',
        ]);

        return $quote;
    }

    /**
     * Return a structured quote analysis for the PR summary view.
     */
    public function getQuoteAnalysis(PurchaseRequisition $pr): array
    {
        $all       = $this->quoteRepo->findByRequisition($pr->id);
        $responded = $this->quoteRepo->findResponded($pr->id);
        $lowest    = $this->quoteRepo->findLowestQuote($pr->id);

        $requiresThree = $pr->requiresThreeQuotes();
        $minRequired   = config('procurement.min_quotes_required', 3);

        $committeeThreshold = config('procurement.committee_threshold', 50000);
        $committeeRequired  = $this->quoteRepo->allQuotesExceedThreshold($pr->id, $committeeThreshold);

        return [
            'total_quotes'          => $all->count(),
            'responded'             => $responded->count(),
            'no_response'           => $all->count() - $responded->count(),
            'lowest_quote'          => $lowest,
            'requires_three_quotes' => $requiresThree,
            'quote_requirement_met' => ! $requiresThree || $responded->count() >= $minRequired,
            'committee_required'    => $committeeRequired,
            'quotes'                => $all,
        ];
    }

    /**
     * Create a Purchase Order for a PR against a chosen supplier.
     */
    public function createPurchaseOrder(
        PurchaseRequisition $pr,
        int $supplierId,
        array $items,
        User $actor,
        ?string $expectedDeliveryDate = null,
    ): PurchaseOrder {
        $poAllowedStatuses = ['pending_procurement', 'pending_vc_payment', 'pending_payment'];
        if (! in_array($pr->status, $poAllowedStatuses, true)) {
            throw new RuntimeException('A Purchase Order can only be raised during procurement or payment stages.');
        }

        return DB::transaction(function () use ($pr, $supplierId, $items, $actor, $expectedDeliveryDate) {
            $totalValue = collect($items)->sum('total_price');

            $po = $this->poRepo->create([
                'purchase_requisition_id' => $pr->id,
                'supplier_id'             => $supplierId,
                'total_value'             => $totalValue,
                'status'                  => 'draft',
                'issued_date'             => now(),
                'expected_delivery_date'  => $expectedDeliveryDate,
            ]);

            foreach ($items as $item) {
                $po->items()->create($item);
            }

            $this->auditService->log(PurchaseOrder::class, $po->id, 'created', [], [
                'po_number'    => $po->po_number,
                'supplier_id'  => $supplierId,
                'total_value'  => $totalValue,
                'created_by'   => $actor->name,
            ]);

            return $po->fresh(['items', 'supplier']);
        });
    }

    /**
     * Mark a PO as issued (sent to supplier).
     */
    public function issuePurchaseOrder(PurchaseOrder $po, User $actor): PurchaseOrder
    {
        $this->poRepo->update($po->id, [
            'status'      => 'issued',
            'issued_date' => now(),
        ]);

        $this->auditService->log(PurchaseOrder::class, $po->id, 'issued', ['status' => 'draft'], ['status' => 'issued']);

        return $po->fresh();
    }
}
