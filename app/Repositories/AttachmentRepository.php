<?php

namespace App\Repositories;

use App\Contracts\AttachmentRepositoryInterface;
use App\Models\Procurement\Attachment;
use Illuminate\Database\Eloquent\Collection;

class AttachmentRepository extends BaseRepository implements AttachmentRepositoryInterface
{
    protected function model(): string
    {
        return Attachment::class;
    }

    public function findByRequisition(int $prId): Collection
    {
        return $this->model
            ->with('uploader')
            ->where('purchase_requisition_id', $prId)
            ->orderBy('created_at')
            ->get();
    }

    public function findByType(int $prId, string $type): Collection
    {
        return $this->model
            ->with('uploader')
            ->where('purchase_requisition_id', $prId)
            ->where('attachment_type', $type)
            ->get();
    }

    public function hasType(int $prId, string $type): bool
    {
        return $this->model
            ->where('purchase_requisition_id', $prId)
            ->where('attachment_type', $type)
            ->exists();
    }
}
