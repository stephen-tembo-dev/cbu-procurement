<?php

namespace App\Contracts;

use App\Models\Procurement\Attachment;
use Illuminate\Database\Eloquent\Collection;

interface AttachmentRepositoryInterface extends BaseRepositoryInterface
{
    public function findByRequisition(int $prId): Collection;

    /** Filter by attachment_type (e.g. 'memo', 'quote_evidence', 'committee_minutes'). */
    public function findByType(int $prId, string $type): Collection;

    /** Whether a PR has at least one attachment of the given type. */
    public function hasType(int $prId, string $type): bool;
}
