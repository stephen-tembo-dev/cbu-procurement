<?php

namespace App\Services;

use App\Contracts\ApprovalRecordRepositoryInterface;
use App\Contracts\AttachmentRepositoryInterface;
use App\Contracts\SupplierQuoteRepositoryInterface;
use App\Models\Procurement\PurchaseRequisition;

class AuditComplianceService
{
    public function __construct(
        private readonly SupplierQuoteRepositoryInterface  $quoteRepo,
        private readonly AttachmentRepositoryInterface     $attachmentRepo,
        private readonly ApprovalRecordRepositoryInterface $approvalRepo,
    ) {}

    /**
     * Run all compliance checks on a PR and return a structured result.
     *
     * @return array{
     *   passed: bool,
     *   flags: array<string, array{passed: bool, message: string}>
     * }
     */
    public function runChecks(PurchaseRequisition $pr): array
    {
        $flags = [
            'quote_count'          => $this->checkQuoteCount($pr),
            'missing_contact_log'  => $this->checkMissingContactLogs($pr),
            'committee_evidence'   => $this->checkCommitteeEvidence($pr),
            'approver_validity'    => $this->checkApproverValidity($pr),
            'service_consumed'     => $this->checkServiceConsumed($pr),
        ];

        $passed = collect($flags)->every(fn ($f) => $f['passed']);

        return compact('passed', 'flags');
    }

    /**
     * PRs above the threshold must have at least min_quotes_required responded quotes.
     */
    public function checkQuoteCount(PurchaseRequisition $pr): array
    {
        $required  = config('procurement.min_quotes_required', 3);
        $threshold = config('procurement.quote_threshold', 10000);

        if ($pr->totalEstimated() < $threshold) {
            return ['passed' => true, 'message' => 'Below quote threshold — no minimum required.'];
        }

        $responded = $this->quoteRepo->findResponded($pr->id)->count();

        if ($responded >= $required) {
            return ['passed' => true, 'message' => "Required {$required} quotes; {$responded} received."];
        }

        return [
            'passed'  => false,
            'message' => "Only {$responded} of {$required} required quotes have been received.",
        ];
    }

    /**
     * Every supplier that was contacted but returned no quote must have contact evidence logged.
     */
    public function checkMissingContactLogs(PurchaseRequisition $pr): array
    {
        $noResponse = $this->quoteRepo->findNoResponse($pr->id);

        $missingEvidence = $noResponse->filter(
            fn ($q) => blank($q->contact_evidence_path)
        );

        if ($missingEvidence->isEmpty()) {
            return ['passed' => true, 'message' => 'All non-responding suppliers have contact evidence on file.'];
        }

        $names = $missingEvidence->map(fn ($q) => $q->supplier->name)->join(', ');

        return [
            'passed'  => false,
            'message' => "Missing contact evidence for: {$names}.",
        ];
    }

    /**
     * If all responded quotes exceed the committee threshold, procurement committee
     * minutes must be attached.
     */
    public function checkCommitteeEvidence(PurchaseRequisition $pr): array
    {
        $committeeThreshold = config('procurement.committee_threshold', 50000);

        $allExceed = $this->quoteRepo->allQuotesExceedThreshold($pr->id, $committeeThreshold);

        if (! $allExceed) {
            return ['passed' => true, 'message' => 'Committee evidence not required.'];
        }

        $hasMinutes = $this->attachmentRepo->hasType($pr->id, 'committee_minutes');

        return $hasMinutes
            ? ['passed' => true,  'message' => 'Procurement committee minutes are on file.']
            : ['passed' => false, 'message' => 'All quotes exceed the threshold — committee minutes must be attached.'];
    }

    /**
     * Verify each stage was approved by a user carrying the correct role.
     */
    public function checkApproverValidity(PurchaseRequisition $pr): array
    {
        $stageRoleMap = [
            'hod'              => 'hod',
            'stores'           => 'stores',
            'bursar'           => 'bursar',
            'vc_requisition'   => 'vc',
            'vc_payment'       => 'vc',
        ];

        $violations = [];

        foreach ($stageRoleMap as $stage => $expectedRole) {
            $record = $this->approvalRepo->findLatestForStage($pr->id, $stage);

            if (! $record) {
                continue; // Stage not yet reached
            }

            if (! $record->actor->hasRole($expectedRole)) {
                $violations[] = "Stage '{$stage}' was actioned by {$record->actor->name}, who does not hold the '{$expectedRole}' role.";
            }
        }

        return empty($violations)
            ? ['passed' => true,  'message' => 'All stages approved by authorised actors.']
            : ['passed' => false, 'message' => implode(' | ', $violations)];
    }

    /**
     * Service-type PRs: flag if service was consumed before the PR was raised.
     */
    public function checkServiceConsumed(PurchaseRequisition $pr): array
    {
        if ($pr->type !== 'service') {
            return ['passed' => true, 'message' => 'Not a service PR — check not applicable.'];
        }

        return $pr->service_consumed_flag
            ? ['passed' => false, 'message' => 'This service was consumed before the PR was raised.']
            : ['passed' => true,  'message' => 'Service was booked in advance — no flag.'];
    }

    /**
     * Write an audit log entry.
     * Currently a no-op — will be wired to AuditLogRepository when audit logging is enabled.
     */
    public function log(
        string $entityType,
        int $entityId,
        string $action,
        array $old = [],
        array $new = []
    ): void {
        // No-op: audit log repository not yet wired.
    }
}
