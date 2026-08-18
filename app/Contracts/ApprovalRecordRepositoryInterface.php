<?php

namespace App\Contracts;

use App\Models\Procurement\ApprovalRecord;
use Illuminate\Database\Eloquent\Collection;

interface ApprovalRecordRepositoryInterface extends BaseRepositoryInterface
{
    /** Full approval trail for a PR, ordered chronologically. */
    public function findByRequisition(int $prId): Collection;

    /** Most recent record for a PR at a given stage. */
    public function findLatestForStage(int $prId, string $stage): ?ApprovalRecord;

    /** Whether the given stage has been completed (any decision recorded). */
    public function stageCompleted(int $prId, string $stage): bool;

    /** The actor IDs that have approved at each stage, keyed by stage. */
    public function getApproverMap(int $prId): array;
}
