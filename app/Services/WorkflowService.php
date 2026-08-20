<?php

namespace App\Services;

use App\Contracts\ApprovalRecordRepositoryInterface;
use App\Contracts\PurchaseRequisitionRepositoryInterface;
use App\Models\HodDelegation;
use App\Models\Procurement\ApprovalRecord;
use App\Models\Procurement\PurchaseRequisition;
use App\Models\StockItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WorkflowService
{
    /**
     * Valid "return for revision" targets per current status.
     * Each stage can send the PR back to any of these earlier stages.
     */
    public const RETURN_TARGETS = [
        'pending_hod'            => ['draft'],
        'pending_stores'         => ['draft'],
        'pending_bursar'         => ['draft'],
        'pending_vc_requisition' => ['pending_bursar', 'draft'],
        'pending_procurement'    => ['draft'],
        'pending_audit'          => ['pending_procurement', 'draft'],
        'pending_vc_payment'     => ['pending_audit', 'pending_procurement', 'draft'],
        'pending_payment'        => ['pending_vc_payment', 'pending_audit', 'draft'],
    ];

    /**
     * Valid status transitions: currentStatus => allowedNextStatuses[].
     * This is the single source of truth for what can follow what.
     */
    private const TRANSITIONS = [
        'draft'                  => ['pending_hod'],
        'pending_hod'            => ['pending_stores', 'pending_bursar', 'rejected'],
        'pending_stores'         => ['issued_from_stores', 'pending_bursar'],
        'pending_bursar'         => ['pending_vc_requisition', 'rejected'],
        'pending_vc_requisition' => ['pending_procurement', 'rejected'],
        'pending_procurement'    => ['pending_audit'],
        'pending_audit'          => ['pending_vc_payment'],
        'pending_vc_payment'     => ['pending_payment', 'suspended'],
        'pending_payment'        => ['paid'],
        'paid'                   => ['delivered'],
    ];

    public function __construct(
        private readonly PurchaseRequisitionRepositoryInterface $prRepo,
        private readonly ApprovalRecordRepositoryInterface      $approvalRepo,
        private readonly AuditComplianceService                 $complianceService,
    ) {}

    // ── Transition guards ──────────────────────────────────────────────────

    public function canTransitionTo(PurchaseRequisition $pr, string $targetStatus): bool
    {
        $allowed = self::TRANSITIONS[$pr->status] ?? [];

        return in_array($targetStatus, $allowed, true);
    }

    public function getAllowedTransitions(PurchaseRequisition $pr): array
    {
        return self::TRANSITIONS[$pr->status] ?? [];
    }

    // ── Stage processors ───────────────────────────────────────────────────

    /**
     * HOD (or their active delegate) approves or rejects the PR.
     */
    public function processHodDecision(
        PurchaseRequisition $pr,
        User $actor,
        string $decision,   // 'approved' | 'rejected'
        ?string $comment = null
    ): ApprovalRecord {
        $this->assertStatus($pr, 'pending_hod');
        $delegation = $this->resolveHodAuthority($pr, $actor);
        $this->requireCommentOnRejection($decision, $comment);

        $nextStatus = $decision === 'approved'
            ? ($pr->type === 'service' ? 'pending_bursar' : 'pending_stores')
            : 'rejected';

        return $this->recordAndAdvance($pr, $actor, 'hod', $decision, $comment, $nextStatus, $delegation?->hod_user_id);
    }

    /**
     * Stores officer checks stock.
     * Decision: 'issued' (in stock) | 'approved' (not in stock, proceed to Bursar).
     */
    public function processStoresCheck(
        PurchaseRequisition $pr,
        User $actor,
        string $decision,   // 'issued' | 'approved'
        ?string $comment = null
    ): ApprovalRecord {
        $this->assertStatus($pr, 'pending_stores');
        $this->assertRole($actor, 'stores');

        $nextStatus = $decision === 'issued' ? 'issued_from_stores' : 'pending_bursar';

        return $this->recordAndAdvance($pr, $actor, 'stores', $decision, $comment, $nextStatus);
    }

    /**
     * Stores officer issues items directly from stock — supports partial issuance.
     * $itemData: [pr_item_id => ['stock_item_id' => X, 'qty_issued' => Y]]
     * Only items with qty_issued > 0 are included. Omitted items remain unfulfilled.
     * If all items are fully covered → status: issued_from_stores.
     * If any item is under-issued or omitted → status: pending_bursar (remainder to procurement).
     */
    public function issueFromStores(
        PurchaseRequisition $pr,
        User $actor,
        array $itemData,
        ?string $comment = null
    ): ApprovalRecord {
        $this->assertStatus($pr, 'pending_stores');
        $this->assertRole($actor, 'stores');

        return DB::transaction(function () use ($pr, $actor, $itemData, $comment) {
            $allItems    = $pr->items()->get();
            $fullyIssued = 0;

            foreach ($itemData as $prItemId => $data) {
                $prItem     = $allItems->find((int) $prItemId);
                if (! $prItem) {
                    throw new RuntimeException("PR item #{$prItemId} not found on this requisition.");
                }
                $stockItem  = StockItem::lockForUpdate()->findOrFail((int) $data['stock_item_id']);
                $qtyToIssue = (float) $data['qty_issued'];

                if ($stockItem->quantity_on_hand < $qtyToIssue) {
                    throw new RuntimeException(
                        "Insufficient stock for \"{$stockItem->description}\": "
                        . "{$stockItem->quantity_on_hand} {$stockItem->unit_of_measure} available, "
                        . "{$qtyToIssue} to issue."
                    );
                }

                $stockItem->decrement('quantity_on_hand', $qtyToIssue);
                $prItem->update([
                    'stock_item_id'   => (int) $data['stock_item_id'],
                    'quantity_issued' => $qtyToIssue,
                ]);

                if ($qtyToIssue >= (float) $prItem->quantity) {
                    $fullyIssued++;
                }
            }

            $allFullyIssued = $fullyIssued === $allItems->count();
            $nextStatus     = $allFullyIssued ? 'issued_from_stores' : 'pending_bursar';
            $decision       = $allFullyIssued ? 'issued' : 'partially_issued';

            return $this->recordAndAdvance($pr, $actor, 'stores', $decision, $comment, $nextStatus);
        });
    }

    /**
     * Bursar confirms funds availability and commits (or refuses) to pay.
     */
    public function processBursarDecision(
        PurchaseRequisition $pr,
        User $actor,
        string $decision,   // 'committed' | 'not_committed'
        ?string $comment = null
    ): ApprovalRecord {
        $this->assertStatus($pr, 'pending_bursar');
        $this->assertRole($actor, 'bursar');
        $this->requireCommentOnRejection($decision === 'not_committed' ? 'rejected' : 'approved', $comment);

        $nextStatus = $decision === 'committed' ? 'pending_vc_requisition' : 'rejected';

        return $this->recordAndAdvance($pr, $actor, 'bursar', $decision, $comment, $nextStatus);
    }

    /**
     * VC approves or rejects the requisition.
     */
    public function processVcRequisitionDecision(
        PurchaseRequisition $pr,
        User $actor,
        string $decision,   // 'approved' | 'rejected'
        ?string $comment = null
    ): ApprovalRecord {
        $this->assertStatus($pr, 'pending_vc_requisition');
        $this->assertRole($actor, 'vc');
        $this->requireCommentOnRejection($decision, $comment);

        $nextStatus = $decision === 'approved' ? 'pending_procurement' : 'rejected';

        return $this->recordAndAdvance($pr, $actor, 'vc_requisition', $decision, $comment, $nextStatus);
    }

    /**
     * Auditor reviews compliance. Decision: 'noted' (pass) | 'rejected' (fail).
     */
    public function processAuditReview(
        PurchaseRequisition $pr,
        User $actor,
        string $decision,
        ?string $comment = null
    ): ApprovalRecord {
        $this->assertStatus($pr, 'pending_audit');
        $this->assertRole($actor, 'auditor');

        // Run automated compliance checks and record the result
        $checks = $this->complianceService->runChecks($pr);

        if (! $checks['passed'] && $decision === 'noted') {
            throw new RuntimeException(
                'Compliance checks have outstanding failures. Audit cannot pass this PR: '
                . collect($checks['flags'])->where('passed', false)->map(fn ($f) => $f['message'])->join(' | ')
            );
        }

        $nextStatus = $decision === 'noted' ? 'pending_vc_payment' : 'rejected';

        return $this->recordAndAdvance($pr, $actor, 'audit', $decision, $comment, $nextStatus);
    }

    /**
     * VC approves or suspends payment.
     */
    public function processVcPaymentDecision(
        PurchaseRequisition $pr,
        User $actor,
        string $decision,   // 'approved' | 'suspended'
        ?string $comment = null
    ): ApprovalRecord {
        $this->assertStatus($pr, 'pending_vc_payment');
        $this->assertRole($actor, 'vc');
        $this->requireCommentOnRejection($decision === 'suspended' ? 'rejected' : 'approved', $comment);

        $nextStatus = $decision === 'approved' ? 'pending_payment' : 'suspended';

        return $this->recordAndAdvance($pr, $actor, 'vc_payment', $decision, $comment, $nextStatus);
    }

    /**
     * Return the eligible "send back" targets for the PR's current stage.
     */
    public function getReturnTargets(PurchaseRequisition $pr): array
    {
        return self::RETURN_TARGETS[$pr->status] ?? [];
    }

    /**
     * Requester resubmits a PR that was returned to draft for revision.
     * Records a 'resubmitted' approval entry and advances the PR back to pending_hod.
     */
    public function resubmit(PurchaseRequisition $pr, User $actor): ApprovalRecord
    {
        if ($pr->status !== 'draft') {
            throw new RuntimeException("PR #{$pr->reference_no} cannot be resubmitted — it is not in draft status.");
        }

        if ($pr->requester_id !== $actor->id && ! $actor->hasRole('admin')) {
            throw new RuntimeException('Only the original requester can resubmit this PR.');
        }

        return $this->recordAndAdvance($pr, $actor, 'draft', 'resubmitted', null, 'pending_hod');
    }

    /**
     * Return a PR to an earlier stage with a mandatory explanation comment.
     * The actor must be the expected reviewer at the current stage.
     * Previous approvals at stages before the target remain in the trail.
     */
    public function processReturnForRevision(
        PurchaseRequisition $pr,
        User $actor,
        string $targetStatus,
        string $comment
    ): ApprovalRecord {
        $eligible = self::RETURN_TARGETS[$pr->status] ?? [];

        if (empty($eligible)) {
            throw new RuntimeException("PR #{$pr->reference_no} cannot be returned at status '{$pr->status}'.");
        }

        if (! in_array($targetStatus, $eligible, true)) {
            throw new RuntimeException("'{$targetStatus}' is not a valid return target from '{$pr->status}'.");
        }

        if (blank($comment)) {
            throw new RuntimeException('A comment is required when returning a PR for revision.');
        }

        $this->assertReturnAuthority($pr, $actor);

        return $this->recordAndAdvance($pr, $actor, $pr->status, 'returned_for_revision', $comment, $targetStatus);
    }

    // ── Internals ──────────────────────────────────────────────────────────

    /**
     * Verify the actor may act as HOD on this PR's department.
     * Returns the active delegation record if the actor is a delegate, null if they are the HOD.
     * Throws if the actor has neither authority.
     */
    private function resolveHodAuthority(PurchaseRequisition $pr, User $actor): ?HodDelegation
    {
        $pr->loadMissing('department');

        if ($pr->department->hod_user_id === $actor->id) {
            if (! $actor->hasRole('hod')) {
                throw new RuntimeException(
                    "User {$actor->name} is recorded as HOD but does not have the 'hod' role assigned."
                );
            }
            return null;
        }

        $delegation = HodDelegation::where('department_id', $pr->department_id)
            ->where('delegate_user_id', $actor->id)
            ->currentlyActive()
            ->first();

        if (! $delegation) {
            throw new RuntimeException(
                "User {$actor->name} is not the HOD of this department and has no active delegation to approve its requisitions."
            );
        }

        return $delegation;
    }

    private function recordAndAdvance(
        PurchaseRequisition $pr,
        User $actor,
        string $stage,
        string $decision,
        ?string $comment,
        string $nextStatus,
        ?int $delegatedBy = null
    ): ApprovalRecord {
        return DB::transaction(function () use ($pr, $actor, $stage, $decision, $comment, $nextStatus, $delegatedBy) {
            $record = $this->approvalRepo->create([
                'purchase_requisition_id' => $pr->id,
                'actor_id'                => $actor->id,
                'delegated_by'            => $delegatedBy,
                'stage'                   => $stage,
                'decision'                => $decision,
                'comment'                 => $comment,
                'acted_at'                => now(),
            ]);

            $this->prRepo->updateStatus($pr->id, $nextStatus);

            $this->complianceService->log(
                PurchaseRequisition::class,
                $pr->id,
                "stage_{$stage}_{$decision}",
                ['status' => $pr->status],
                ['status' => $nextStatus]
            );

            return $record;
        });
    }

    private function assertReturnAuthority(PurchaseRequisition $pr, User $actor): void
    {
        $authorised = match ($pr->status) {
            'pending_hod'            => $this->actorCanActAsHod($pr, $actor),
            'pending_stores'         => $actor->hasRole('stores'),
            'pending_bursar'         => $actor->hasRole('bursar'),
            'pending_vc_requisition' => $actor->hasRole('vc'),
            'pending_procurement'    => $actor->hasRole('procurement'),
            'pending_audit'          => $actor->hasRole('auditor'),
            'pending_vc_payment'     => $actor->hasRole('vc'),
            'pending_payment'        => $actor->hasRole('bursar'),
            default                  => false,
        };

        if (! $authorised) {
            throw new RuntimeException(
                "User {$actor->name} does not have authority to return this PR at its current stage."
            );
        }
    }

    private function actorCanActAsHod(PurchaseRequisition $pr, User $actor): bool
    {
        $pr->loadMissing('department');

        if ($pr->department->hod_user_id === $actor->id && $actor->hasRole('hod')) {
            return true;
        }

        return HodDelegation::where('department_id', $pr->department_id)
            ->where('delegate_user_id', $actor->id)
            ->currentlyActive()
            ->exists();
    }

    private function assertStatus(PurchaseRequisition $pr, string $expected): void
    {
        if ($pr->status !== $expected) {
            throw new RuntimeException(
                "Cannot process: PR #{$pr->reference_no} is in status '{$pr->status}', expected '{$expected}'."
            );
        }
    }

    private function assertRole(User $actor, string $role): void
    {
        if (! $actor->hasRole($role)) {
            throw new RuntimeException(
                "User {$actor->name} does not have the required '{$role}' role to perform this action."
            );
        }
    }

    private function requireCommentOnRejection(string $decision, ?string $comment): void
    {
        if ($decision === 'rejected' && blank($comment)) {
            throw new RuntimeException('A comment is required when rejecting a purchase requisition.');
        }
    }
}
