<?php

namespace App\Services;

use App\Contracts\AttachmentRepositoryInterface;
use App\Models\Procurement\Attachment;
use App\Models\Procurement\PurchaseRequisition;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class AttachmentService
{
    // Permitted MIME types
    private const ALLOWED_MIMES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/jpeg',
        'image/png',
    ];

    // Max size in bytes (5 MB)
    private const MAX_SIZE = 5 * 1024 * 1024;

    public function __construct(
        private readonly AttachmentRepositoryInterface $attachmentRepo,
        private readonly AuditComplianceService        $auditService,
    ) {}

    /**
     * Validate, store and record an attachment.
     *
     * @param  string  $type  e.g. 'memo' | 'quote_evidence' | 'committee_minutes' | 'specification'
     */
    public function upload(
        PurchaseRequisition $pr,
        UploadedFile $file,
        string $type,
        User $uploader
    ): Attachment {
        $this->validateFile($file);

        $path = $file->store(
            "procurement/pr-{$pr->id}/{$type}",
            'private'
        );

        $attachment = $this->attachmentRepo->create([
            'purchase_requisition_id' => $pr->id,
            'uploaded_by'             => $uploader->id,
            'file_name'               => $file->getClientOriginalName(),
            'file_path'               => $path,
            'mime_type'               => $file->getMimeType(),
            'file_size'               => $file->getSize(),
            'attachment_type'         => $type,
        ]);

        $this->auditService->log(PurchaseRequisition::class, $pr->id, 'attachment_uploaded', [], [
            'attachment_id' => $attachment->id,
            'file_name'     => $attachment->file_name,
            'type'          => $type,
            'uploaded_by'   => $uploader->name,
        ]);

        return $attachment;
    }

    /**
     * Delete an attachment and remove the file from storage.
     */
    public function delete(int $attachmentId, User $actor): bool
    {
        $attachment = $this->attachmentRepo->findById($attachmentId);

        if (! $attachment) {
            throw new RuntimeException("Attachment #{$attachmentId} not found.");
        }

        Storage::disk('private')->delete($attachment->file_path);

        $this->auditService->log(
            PurchaseRequisition::class,
            $attachment->purchase_requisition_id,
            'attachment_deleted',
            ['file_name' => $attachment->file_name],
            ['deleted_by' => $actor->name]
        );

        return $this->attachmentRepo->delete($attachmentId);
    }

    /**
     * Return a temporary signed URL for downloading an attachment.
     */
    public function temporaryUrl(Attachment $attachment, int $minutes = 5): string
    {
        return Storage::disk('private')->temporaryUrl($attachment->file_path, now()->addMinutes($minutes));
    }

    private function validateFile(UploadedFile $file): void
    {
        if (! in_array($file->getMimeType(), self::ALLOWED_MIMES, true)) {
            throw new RuntimeException('File type not permitted. Allowed: PDF, Word documents, JPEG, PNG.');
        }

        if ($file->getSize() > self::MAX_SIZE) {
            throw new RuntimeException('File exceeds the maximum allowed size of 5 MB.');
        }
    }
}
