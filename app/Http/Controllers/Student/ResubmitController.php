<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Enums\UserRole;
use App\Mail\LoaResubmittedStaffMail;
use App\Mail\LoaSubmittedMail;
use App\Models\Department;
use App\Models\LoaRequest;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ResubmitController extends Controller
{
    // ── Show the pre-filled resubmit form ─────────────────────────────────────

    public function show(string $token): View|RedirectResponse
    {
        $loa = $this->resolve($token);

        if ($loa instanceof RedirectResponse) {
            return $loa;
        }

        $loa->load(['department', 'program', 'rejectedByActor', 'attachments']);

        return view('student.resubmit', [
            'token'       => $token,
            'loa'         => $loa,
            'departments' => Department::query()->orderBy('name')->get(),
            'programs'    => Program::query()->orderBy('name')->get(['id', 'department_id', 'code', 'name']),
        ]);
    }

    // ── Handle resubmission ───────────────────────────────────────────────────

    public function store(Request $request, string $token): RedirectResponse
    {
        $loa = $this->resolve($token);

        if ($loa instanceof RedirectResponse) {
            return $loa;
        }

        $validated = $request->validate([
            'department_id'      => ['required', 'exists:departments,id'],
            'program_id'         => [
                'required',
                Rule::exists('programs', 'id')->where('department_id', $request->integer('department_id')),
            ],
            'year_level'         => ['required', 'string', 'max:20'],
            'start_date'         => ['required', 'date'],
            'return_date'        => ['required', 'date', 'after:start_date'],
            'reason'             => ['required', 'string', 'max:5000'],
            'parent_full_name'   => ['required', 'string', 'max:255'],
            'parent_relationship'=> ['required', 'string', 'max:100'],
            'parent_phone'       => ['required', 'string', 'regex:/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/'],
            'attachments'        => ['nullable', 'array', 'max:10'],
            'attachments.*'      => ['file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'parent_phone.regex' => 'Enter a valid 10-digit Philippine mobile number, e.g. 917-123-4567.',
        ]);

        $maxReturn = now()->parse($validated['start_date'])->addYear();
        if (now()->parse($validated['return_date'])->gt($maxReturn)) {
            return back()
                ->withInput()
                ->withErrors(['return_date' => 'The approved leave period cannot exceed one year.']);
        }

        // Normalize phone: strip leading zero exactly once, then prepend +63
        $phone = preg_replace('/[\s\-]/', '', $validated['parent_phone']);
        $phone = ltrim($phone, '0');
        if (! str_starts_with($phone, '+63')) {
            $phone = '+63' . $phone;
        }

        // Update mutable fields + reset pipeline via model method
        $loa->resubmit([
            'department_id'       => $validated['department_id'],
            'program_id'          => $validated['program_id'],
            'year_level'          => $validated['year_level'],
            'reason'              => $validated['reason'],
            'start_date'          => $validated['start_date'],
            'return_date'         => $validated['return_date'],
            'parent_full_name'    => $validated['parent_full_name'],
            'parent_relationship' => $validated['parent_relationship'],
            'parent_phone'        => $phone,
        ]);

        // Handle new attachments if provided
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('loa-attachments/' . $loa->id, 'local');

                $safeName = mb_substr(
                    preg_replace('/[^\w.\-_ ]/u', '_', basename((string) $file->getClientOriginalName())),
                    0, 255
                );

                $loa->attachments()->create([
                    'original_name' => $safeName ?: 'attachment',
                    'path'          => $path,
                    'mime_type'     => $file->getMimeType() ?? 'application/octet-stream',
                    'size'          => $file->getSize(),
                ]);
            }
        }

        // Send confirmation email to student.
        $fresh = $loa->fresh();
        try {
            $fresh->load(['department', 'program', 'attachments']);
            Mail::to($fresh->email)->send(new LoaSubmittedMail($fresh));
        } catch (\Throwable $e) {
            report($e);
        }

        // Notify the staff who need to act on the resubmission.
        // Find users whose role matches the now-pending stage.
        try {
            $this->notifyPendingStageStaff($fresh);
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()
            ->route('student.resubmit.done', $token)
            ->with('control_number', $fresh->control_number);
    }

    // ── Confirmation page ─────────────────────────────────────────────────────

    public function done(string $token): View|RedirectResponse
    {
        // Token is consumed at this point — loa is no longer rejected
        // We just show a generic confirmation. If direct navigation, redirect home.
        $controlNumber = session('control_number');
        if (! $controlNumber) {
            return redirect()->route('home');
        }

        return view('student.resubmit-done', [
            'control_number' => $controlNumber,
        ]);
    }

    // ── Staff resubmit notification ───────────────────────────────────────────

    private function notifyPendingStageStaff(LoaRequest $loa): void
    {
        $fresh = $loa->fresh(['department']);

        // Determine the pending stage
        $stage = $fresh->determineCurrentStage();

        $roleMap = [
            'dept_head'       => UserRole::DepartmentHead,
            'saso'            => UserRole::SasoOfficer,
            'campus_director' => UserRole::CampusDirector,
        ];

        if (! isset($roleMap[$stage])) {
            return;
        }

        $targetRole = $roleMap[$stage];

        $staffQuery = User::query()
            ->where('role', $targetRole->value)
            ->whereNotNull('invitation_accepted_at');

        // DH is dept-scoped — only notify the DH of the student's department
        if ($targetRole === UserRole::DepartmentHead && $fresh->department_id) {
            $staffQuery->where('department_id', $fresh->department_id);
        }

        $staffQuery->each(function (User $staff) use ($fresh) {
            Mail::to($staff->email)->send(new LoaResubmittedStaffMail($fresh, $staff));
        });
    }

    // ── Token resolver ────────────────────────────────────────────────────────

    private function resolve(string $token): LoaRequest|RedirectResponse
    {
        if (! preg_match('/^[A-Za-z0-9]{40}$/', $token)) {
            return redirect()
                ->route('home')
                ->withErrors(['token' => 'That resubmit link is invalid. Please use the link from your rejection email.']);
        }

        $loa = LoaRequest::findByResubmitToken($token);

        if (! $loa) {
            return redirect()
                ->route('home')
                ->withErrors(['token' => 'That resubmit link is invalid or has expired. Links are valid for 7 days after the rejection email is sent.']);
        }

        return $loa;
    }
}
