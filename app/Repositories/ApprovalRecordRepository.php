<?php

namespace App\Repositories;

use App\Contracts\ApprovalRecordRepositoryInterface;
use App\Models\Procurement\ApprovalRecord;
use Illuminate\Database\Eloquent\Collection;

class ApprovalRecordRepository extends BaseRepository implements ApprovalRecordRepositoryInterface
{
    protected function model(): string
    {
        return ApprovalRecord::class;
    }

    public function findByRequisition(int $prId): Collection
    {
        return $this->model
            ->with('actor')
            ->where('purchase_requisition_id', $prId)
            ->orderBy('created_at')
            ->get();
    }

    public function findLatestForStage(int $prId, string $stage): ?ApprovalRecord
    {
        return $this->model
            ->with('actor')
            ->where('purchase_requisition_id', $prId)
            ->where('stage', $stage)
            ->latest('created_at')
            ->first();
    }

    public function stageCompleted(int $prId, string $stage): bool
    {
        return $this->model
            ->where('purchase_requisition_id', $prId)
            ->where('stage', $stage)
            ->exists();
    }

    public function getApproverMap(int $prId): array
    {
        return $this->model
            ->where('purchase_requisition_id', $prId)
            ->pluck('actor_id', 'stage')
            ->toArray();
    }
}
