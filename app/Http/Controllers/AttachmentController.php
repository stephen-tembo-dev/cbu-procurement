<?php

namespace App\Http\Controllers;

use App\Models\Procurement\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function download(Request $request, Attachment $attachment)
    {
        $user = auth()->user();
        $pr   = $attachment->purchaseRequisition;

        abort_unless($pr, 404);

        abort_unless(
            $pr->requester_id === $user->id
            || $user->hasAnyRole(['hod', 'stores', 'bursar', 'vc', 'procurement', 'auditor', 'admin']),
            403
        );

        abort_unless(Storage::disk('private')->exists($attachment->file_path), 404);

        return Storage::disk('private')->download(
            $attachment->file_path,
            $attachment->file_name,
            ['Content-Type' => $attachment->mime_type]
        );
    }
}
