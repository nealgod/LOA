<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\LoaAttachment;
use App\Models\LoaRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class LoaRequestController extends Controller
{
    public function show(LoaRequest $loaRequest): View|RedirectResponse
    {
        abort_unless(auth()->user()->canViewLoaRequest($loaRequest), 403);

        $loaRequest->load(['department', 'program', 'attachments']);

        return view('staff.loa-show', [
            'loa' => $loaRequest,
        ]);
    }

    public function attachment(LoaRequest $loaRequest, LoaAttachment $attachment): StreamedResponse
    {
        abort_unless(auth()->user()->canViewLoaRequest($loaRequest), 403);
        abort_unless($attachment->loa_request_id === $loaRequest->id, 404);

        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        return Storage::disk('local')->download($attachment->path, $attachment->original_name);
    }
}
