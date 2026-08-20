<?php

use App\Contracts\AttachmentRepositoryInterface;
use App\Contracts\PurchaseRequisitionRepositoryInterface;
use App\Models\Finance\BudgetAllocation;
use App\Models\Procurement\PurchaseRequisition;
use App\Services\AttachmentService;
use App\Services\AuditComplianceService;
use App\Services\BudgetService;
use App\Services\PaymentService;
use App\Services\ProcurementService;
use App\Services\WorkflowService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public int     $prId;
    public bool    $showActionModal  = false;
    public string  $pendingDecision  = '';
    public string  $actionComment    = '';
    public ?string $flash            = null;
    public ?string $flashType        = null;

    // Return for revision
    public bool   $showReturnModal    = false;
    public string $returnTargetStatus = '';
    public string $returnComment      = '';

    // Attachment upload
    public bool   $showUploadModal = false;
    public        $uploadFile      = null;
    public string $uploadType      = '';

    // Stores issuance
    public bool   $showStoresIssuanceForm = false;
    public array  $storesLinks            = [];  // [pr_item_id => stock_item_id]
    public array  $storesQtyIssued        = [];  // [pr_item_id => quantity string]
    public string $storesComment          = '';

    // Procurement-specific
    public bool   $showQuoteForm     = false;
    public ?int   $editingQuoteId    = null;
    public int    $quoteSupplier     = 0;
    public string $quoteAmount       = '';
    public string $quoteDate         = '';
    public string $quoteNotes        = '';

    public function mount(int $id): void
    {
        $this->prId = $id;

        $pr = app(PurchaseRequisitionRepositoryInterface::class)->findById($id);

        abort_if(! $pr, 404);

        // Requesters can only view their own PRs (unless they have a broader role)
        $user = auth()->user();
        if (
            ! $user->hasAnyRole(['hod', 'stores', 'bursar', 'vc', 'procurement', 'auditor', 'admin'])
            && $pr->requester_id !== $user->id
        ) {
            abort(403);
        }

        if (session()->has('success')) {
            $this->flash     = session('success');
            $this->flashType = 'success';
        }
    }

    #[Computed]
    public function pr(): PurchaseRequisition
    {
        return app(PurchaseRequisitionRepositoryInterface::class)->findWithFullTrail($this->prId);
    }

    // ── Modal control ────────────────────────────────────────────────────────

    public function openAction(string $decision): void
    {
        $this->pendingDecision = $decision;
        $this->actionComment   = '';
        $this->showActionModal = true;
    }

    public function cancelAction(): void
    {
        $this->showActionModal = false;
        $this->pendingDecision = '';
        $this->actionComment   = '';
    }

    // ── Resubmit after revision ──────────────────────────────────────────────

    public function resubmit(): void
    {
        try {
            app(WorkflowService::class)->resubmit($this->pr, auth()->user());

            unset($this->pr);
            $this->flash     = 'PR resubmitted for approval.';
            $this->flashType = 'success';
        } catch (RuntimeException $e) {
            $this->flash     = $e->getMessage();
            $this->flashType = 'error';
        }
    }

    // ── Return for revision ──────────────────────────────────────────────────

    #[Computed]
    public function returnTargets(): array
    {
        return app(WorkflowService::class)->getReturnTargets($this->pr);
    }

    public function openReturnModal(): void
    {
        $this->returnTargetStatus = '';
        $this->returnComment      = '';
        $this->showReturnModal    = true;
    }

    public function cancelReturnModal(): void
    {
        $this->showReturnModal    = false;
        $this->returnTargetStatus = '';
        $this->returnComment      = '';
    }

    public function confirmReturn(): void
    {
        $this->validate([
            'returnTargetStatus' => 'required|string',
            'returnComment'      => 'required|string|min:10',
        ], [
            'returnTargetStatus.required' => 'Please select where to return the PR.',
            'returnComment.required'      => 'A comment explaining the issue is required.',
            'returnComment.min'           => 'Please provide a more detailed explanation (at least 10 characters).',
        ]);

        try {
            app(WorkflowService::class)->processReturnForRevision(
                $this->pr,
                auth()->user(),
                $this->returnTargetStatus,
                $this->returnComment,
            );

            unset($this->pr);
            $this->showReturnModal    = false;
            $this->returnTargetStatus = '';
            $this->returnComment      = '';
            $this->flash              = 'PR returned for revision. Your comments have been recorded in the approval trail.';
            $this->flashType          = 'success';
        } catch (RuntimeException $e) {
            $this->addError('returnComment', $e->getMessage());
        }
    }

    // ── Stores issuance ──────────────────────────────────────────────────────

    public function openStoresIssuance(): void
    {
        $items = $this->pr->items;

        $this->storesLinks     = $items->mapWithKeys(fn ($item) => [$item->id => $item->stock_item_id ?? ''])->toArray();
        $this->storesQtyIssued = $items->mapWithKeys(fn ($item) => [$item->id => (string) $item->quantity])->toArray();
        $this->storesComment          = '';
        $this->showStoresIssuanceForm = true;
    }

    public function cancelStoresIssuance(): void
    {
        $this->showStoresIssuanceForm = false;
        $this->storesLinks            = [];
        $this->storesQtyIssued        = [];
        $this->storesComment          = '';
    }

    public function confirmStoresIssuance(): void
    {
        $user = auth()->user();
        abort_unless($user->hasRole('stores'), 403);

        $pr      = $this->pr;
        $itemMap = $pr->items->keyBy('id');

        // Per-item validation
        $hasAnyIssued = false;
        foreach ($this->storesQtyIssued as $itemId => $qty) {
            $item     = $itemMap->get((int) $itemId);
            $qtyFloat = (float) $qty;

            if ($qtyFloat < 0) {
                $this->addError("storesQtyIssued.{$itemId}", 'Quantity cannot be negative.');
            } elseif ($item && $qtyFloat > (float) $item->quantity) {
                $this->addError("storesQtyIssued.{$itemId}", 'Cannot exceed the requested quantity.');
            }

            if ($qtyFloat > 0) {
                $hasAnyIssued = true;
                if (empty($this->storesLinks[$itemId])) {
                    $this->addError("storesLinks.{$itemId}", 'Select a stock item for this line.');
                }
            }
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        if (! $hasAnyIssued) {
            $this->flash     = 'Enter a quantity for at least one item. Use "Not in Stock" to forward everything to procurement.';
            $this->flashType = 'error';
            return;
        }

        // Build itemData — only items with qty > 0
        $itemData = [];
        foreach ($this->storesQtyIssued as $itemId => $qty) {
            $qtyFloat = (float) $qty;
            if ($qtyFloat > 0) {
                $itemData[(int) $itemId] = [
                    'stock_item_id' => (int) $this->storesLinks[$itemId],
                    'qty_issued'    => $qtyFloat,
                ];
            }
        }

        try {
            app(WorkflowService::class)->issueFromStores(
                $pr,
                $user,
                $itemData,
                $this->storesComment ?: null,
            );

            unset($this->pr);
            $this->showStoresIssuanceForm = false;
            $this->storesLinks            = [];
            $this->storesQtyIssued        = [];
            $this->storesComment          = '';
            $this->flash                  = 'Stores issuance recorded. Inventory has been updated.';
            $this->flashType              = 'success';
        } catch (RuntimeException $e) {
            $this->showStoresIssuanceForm = false;
            $this->flash                  = $e->getMessage();
            $this->flashType              = 'error';
        }
    }

    #[Computed]
    public function stockedItems(): \Illuminate\Support\Collection
    {
        return \App\Models\StockItem::where('is_stocked', true)
            ->where('is_active', true)
            ->orderBy('description')
            ->get(['id', 'stock_code', 'description', 'quantity_on_hand', 'unit_of_measure']);
    }

    // ── Workflow actions ─────────────────────────────────────────────────────

    public function confirmAction(): void
    {
        $needsComment = in_array($this->pendingDecision, [
            'rejected', 'not_committed', 'suspended',
        ], true);

        if ($needsComment && blank($this->actionComment)) {
            $this->addError('actionComment', 'A comment is required for this decision.');
            return;
        }

        try {
            $this->executeWorkflowAction();
            unset($this->pr);
            $this->showActionModal = false;
            $this->pendingDecision = '';
            $this->actionComment   = '';
            $this->flash           = 'Action completed successfully.';
            $this->flashType       = 'success';
        } catch (RuntimeException $e) {
            $this->addError('actionComment', $e->getMessage());
        }
    }

    private function executeWorkflowAction(): void
    {
        $pr       = $this->pr;
        $user     = auth()->user();
        $workflow = app(WorkflowService::class);
        $comment  = $this->actionComment ?: null;

        match ($pr->status) {
            'pending_hod'            => $workflow->processHodDecision($pr, $user, $this->pendingDecision, $comment),
            'pending_stores'         => $workflow->processStoresCheck($pr, $user, $this->pendingDecision, $comment),
            'pending_bursar'         => $this->handleBursarAction($pr, $user, $comment, $workflow),
            'pending_vc_requisition' => $workflow->processVcRequisitionDecision($pr, $user, $this->pendingDecision, $comment),
            'pending_audit'          => $workflow->processAuditReview($pr, $user, $this->pendingDecision, $comment),
            'pending_vc_payment'     => $this->handleVcPaymentAction($pr, $user, $comment, $workflow),
            'pending_payment'        => $this->handlePayment($pr, $user),
            default                  => throw new RuntimeException('No workflow action available for the current status.'),
        };
    }

    private function handleBursarAction(PurchaseRequisition $pr, $user, ?string $comment, WorkflowService $workflow): void
    {
        if ($this->pendingDecision === 'committed') {
            DB::transaction(function () use ($pr, $user, $comment, $workflow) {
                // Commit only the unfulfilled value — items already issued from stores need no budget.
                app(BudgetService::class)->commitFunds($pr->cost_centre_id, $pr->unfulfilledValue());
                $workflow->processBursarDecision($pr, $user, 'committed', $comment);
            });
        } else {
            $workflow->processBursarDecision($pr, $user, 'not_committed', $comment);
        }
    }

    private function handleVcPaymentAction(PurchaseRequisition $pr, $user, ?string $comment, WorkflowService $workflow): void
    {
        $workflow->processVcPaymentDecision($pr, $user, $this->pendingDecision, $comment);

        if ($this->pendingDecision === 'approved' && $pr->purchaseOrder) {
            $paymentRepo = app(\App\Contracts\PaymentRepositoryInterface::class);
            $payment     = $paymentRepo->findByPurchaseOrder($pr->purchaseOrder->id);

            if (! $payment) {
                $payment = app(PaymentService::class)->initiate($pr->purchaseOrder);
            }

            app(PaymentService::class)->recordVcApproval($payment, $user);
        }
    }

    private function handlePayment(PurchaseRequisition $pr, $user): void
    {
        if (! $pr->purchaseOrder) {
            throw new RuntimeException(
                'No Purchase Order has been raised for this requisition. '
                . 'Procurement must create one before payment can be processed.'
            );
        }

        $payment = app(\App\Contracts\PaymentRepositoryInterface::class)
            ->findByPurchaseOrder($pr->purchaseOrder->id);

        if (! $payment) {
            throw new RuntimeException('No payment record found. VC approval may be missing.');
        }

        $fundsAvailable = $this->pendingDecision === 'paid';

        app(PaymentService::class)->processBursarConfirmation(
            $payment,
            $user,
            $fundsAvailable,
            $this->actionComment ?: null
        );
    }

    // ── Procurement: submit to audit ─────────────────────────────────────────

    public function submitToAudit(): void
    {
        $pr      = $this->pr;
        $user    = auth()->user();
        $comment = $this->actionComment ?: null;

        if ($pr->status !== 'pending_procurement') {
            $this->flash     = 'This PR is not in procurement review.';
            $this->flashType = 'error';
            return;
        }

        if (! $user->hasRole('procurement')) {
            abort(403);
        }

        if (! $pr->purchaseOrder) {
            $this->flash     = 'Create a Purchase Order before submitting this requisition for audit.';
            $this->flashType = 'error';
            return;
        }

        if (! $pr->attachments()->where('attachment_type', 'quote_evidence')->exists()) {
            $this->flash     = 'Attach quote evidence before submitting this requisition for audit.';
            $this->flashType = 'error';
            return;
        }

        $analysis = app(ProcurementService::class)->getQuoteAnalysis($pr);

        if (! $analysis['quote_requirement_met']) {
            $this->flash     = 'Minimum quote requirements have not been met. Add more supplier quotes.';
            $this->flashType = 'error';
            return;
        }

        app(PurchaseRequisitionRepositoryInterface::class)->updateStatus($pr->id, 'pending_audit');
        app(AuditComplianceService::class)->log(
            PurchaseRequisition::class, $pr->id,
            'submitted_to_audit',
            ['status' => 'pending_procurement'],
            ['status' => 'pending_audit']
        );

        unset($this->pr);
        $this->flash     = 'PR submitted for audit review.';
        $this->flashType = 'success';
    }

    // ── Procurement: add quote ───────────────────────────────────────────────

    public function openQuoteForm(): void
    {
        abort_unless(auth()->user()->hasRole('procurement'), 403);

        $this->editingQuoteId = null;
        $this->quoteSupplier = 0;
        $this->quoteAmount   = '';
        $this->quoteDate     = '';
        $this->quoteNotes    = '';
        $this->showQuoteForm = true;
    }

    public function cancelQuote(): void
    {
        $this->showQuoteForm = false;
        $this->editingQuoteId = null;
    }

    public function saveQuote(): void
    {
        abort_unless(auth()->user()->hasRole('procurement'), 403);

        $this->validate([
            'quoteSupplier' => 'required|exists:suppliers,id',
            'quoteAmount'   => 'nullable|numeric|min:0',
            'quoteDate'     => 'nullable|date',
        ]);

        $pr = $this->pr;

        try {
            $payload = [
                'supplier_id'   => $this->quoteSupplier,
                'amount'        => $this->quoteAmount ?: null,
                'received_date' => $this->quoteDate ?: null,
                'notes'         => $this->quoteNotes ?: null,
            ];

            if ($this->editingQuoteId) {
                $quote = $pr->supplierQuotes->firstWhere('id', $this->editingQuoteId);

                if (! $quote) {
                    throw new RuntimeException('The selected quote is no longer available.');
                }

                app(ProcurementService::class)->updateQuote(
                    $quote,
                    $payload,
                );
            } else {
                app(ProcurementService::class)->addQuote($pr, $payload);
            }
        } catch (RuntimeException $e) {
            $this->addError('quoteSupplier', $e->getMessage());

            return;
        }

        unset($this->pr);
        $this->showQuoteForm = false;
        $this->flash         = $this->editingQuoteId ? 'Quote updated successfully.' : 'Quote recorded successfully.';
        $this->flashType     = 'success';
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    #[Computed]
    public function costCentreBudget(): ?BudgetAllocation
    {
        $pr = $this->pr;
        if (! $pr->cost_centre_id) {
            return null;
        }

        return BudgetAllocation::with('costCentre')
            ->where('cost_centre_id', $pr->cost_centre_id)
            ->where('fiscal_year', now()->year)
            ->first();
    }

    #[Computed]
    public function suppliers(): array
    {
        return \App\Models\Supplier::where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function dismissFlash(): void
    {
        $this->flash     = null;
        $this->flashType = null;
    }

    // ── Attachments ──────────────────────────────────────────────────────────

    public function openUploadModal(): void
    {
        $this->uploadFile  = null;
        $this->uploadType  = '';
        $this->showUploadModal = true;
    }

    public function cancelUpload(): void
    {
        $this->showUploadModal = false;
        $this->uploadFile      = null;
        $this->uploadType      = '';
    }

    public function submitUpload(): void
    {
        $this->validate([
            'uploadType' => 'required|in:memo,quote_evidence,committee_minutes,specification',
            'uploadFile' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ], [
            'uploadType.required' => 'Please select a document type.',
            'uploadFile.required' => 'Please select a file to upload.',
            'uploadFile.mimes'    => 'Only PDF, Word, JPEG, and PNG files are allowed.',
            'uploadFile.max'      => 'File must not exceed 5 MB.',
        ]);

        $pr           = $this->pr;
        $type         = $this->uploadType;
        $originalName = $this->uploadFile->getClientOriginalName();
        $extension    = $this->uploadFile->getClientOriginalExtension();
        $uniqueName   = uniqid('', true) . '.' . $extension;
        $finalPath    = "procurement/pr-{$pr->id}/{$type}/{$uniqueName}";

        Storage::disk('private')->put($finalPath, $this->uploadFile->get());

        app(AttachmentRepositoryInterface::class)->create([
            'purchase_requisition_id' => $pr->id,
            'uploaded_by'             => auth()->id(),
            'file_name'               => $originalName,
            'file_path'               => $finalPath,
            'mime_type'               => $this->uploadFile->getMimeType(),
            'file_size'               => $this->uploadFile->getSize(),
            'attachment_type'         => $type,
        ]);

        unset($this->pr);
        $this->showUploadModal = false;
        $this->uploadFile      = null;
        $this->uploadType      = '';
        $this->flash     = 'Attachment uploaded successfully.';
        $this->flashType = 'success';
    }

    public function deleteAttachment(int $attachmentId): void
    {
        $user       = auth()->user();
        $attachment = app(AttachmentRepositoryInterface::class)->findById($attachmentId);

        abort_unless($attachment && $attachment->purchase_requisition_id === $this->prId, 404);
        abort_unless($attachment->uploaded_by === $user->id || $user->hasRole('admin'), 403);

        // Once the PO has been issued, attachments form part of the audit trail and cannot be removed.
        $pr = $this->pr;
        if ($pr->purchaseOrder && in_array($pr->purchaseOrder->status, ['issued', 'partially_delivered', 'delivered'])) {
            $this->flash     = 'Attachments cannot be deleted after the Purchase Order has been issued — they form part of the procurement audit trail.';
            $this->flashType = 'error';
            return;
        }

        app(AttachmentService::class)->delete($attachmentId, $user);

        unset($this->pr);
        $this->flash     = 'Attachment deleted.';
        $this->flashType = 'success';
    }
};
