<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\LoaAttachment;
use App\Models\LoaRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LoaRequestController extends Controller
{
    public function pipeline(): View
    {
        $user = auth()->user();

        $loas = LoaRequest::query()
            ->with(['department', 'program', 'deptHeadActor', 'sasoActor', 'campusDirectorActor', 'registrarActor', 'guidanceActor', 'rejectedByActor'])
            ->scopeForUser($user)
            ->latest('submitted_at')
            ->get();

        return view('staff.pipeline', compact('user', 'loas'));
    }

    public function show(LoaRequest $loaRequest): View|RedirectResponse
    {
        abort_unless(auth()->user()->canViewLoaRequest($loaRequest), 403);

        $loaRequest->load([
            'department',
            'program',
            'attachments',
            'deptHeadActor',
            'sasoActor',
            'campusDirectorActor',
            'registrarActor',
            'guidanceActor',
            'rejectedByActor',
        ]);

        return view('staff.loa-show', [
            'loa' => $loaRequest,
        ]);
    }

    public function attachment(LoaRequest $loaRequest, LoaAttachment $attachment): StreamedResponse
    {
        // All authenticated staff can view attachments — only DH is dept-scoped.
        // The dept.scope middleware already blocked cross-dept DH before reaching here.
        abort_unless(auth()->user()->canViewLoaRequest($loaRequest), 403);
        abort_unless($attachment->loa_request_id === $loaRequest->id, 404);
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        return Storage::disk('local')->download($attachment->path, $attachment->original_name);
    }

    public function approve(Request $request, LoaRequest $loaRequest): RedirectResponse
    {
        $this->authorize('approve', $loaRequest);

        $user = $request->user();
        $loaRequest->markApprovedBy($user);

        return redirect()->route('staff.pipeline')->with('status', "LOA {$loaRequest->control_number} approved by {$user->role->label()}.");
    }

    public function reject(Request $request, LoaRequest $loaRequest): RedirectResponse
    {
        $this->authorize('reject', $loaRequest);

        $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ], [
            'reason.required' => 'A rejection reason is required.',
        ]);

        $user = $request->user();
        $loaRequest->markRejectedBy($user, $request->input('reason'));

        return redirect()->route('staff.pipeline')->with('status', "LOA {$loaRequest->control_number} rejected by {$user->role->label()}.");
    }
}
