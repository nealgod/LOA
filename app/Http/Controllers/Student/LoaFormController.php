<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Enums\ApprovalStageStatus;
use App\Mail\LoaSubmittedMail;
use App\Models\Department;
use App\Models\LoaAccessToken;
use App\Models\LoaRequest;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LoaFormController extends Controller
{
    public function show(string $token): View|RedirectResponse
    {
        $access = $this->resolveToken($token);

        if ($access instanceof RedirectResponse) {
            return $access;
        }

        $request = $access->loaRequest ?? LoaRequest::create([
            'loa_access_token_id' => $access->id,
            'student_id' => $access->student_id,
            'full_name' => $access->full_name,
            'email' => $access->email,
            'status' => 'draft',
        ]);

        if ($request->isSubmitted()) {
            return redirect()->route('student.form.submitted', ['token' => $token]);
        }

        return view('student.form', [
            'token' => $token,
            'access' => $access,
            'loa' => $request,
            'departments' => Department::query()->orderBy('name')->get(),
            'programs' => Program::query()->orderBy('name')->get(['id', 'department_id', 'code', 'name']),
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $access = $this->resolveToken($token);

        if ($access instanceof RedirectResponse) {
            return $access;
        }

        $loa = $access->loaRequest;

        if (! $loa) {
            return redirect()->route('student.form.show', ['token' => $token]);
        }

        if ($loa->isSubmitted()) {
            return redirect()->route('student.form.submitted', ['token' => $token]);
        }

        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'program_id' => [
                'required',
                Rule::exists('programs', 'id')->where('department_id', $request->integer('department_id')),
            ],
            'year_level' => ['required', 'string', 'max:20'],
            'full_name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'return_date' => ['required', 'date', 'after:start_date'],
            'reason' => ['required', 'string', 'max:5000'],
            'parent_full_name' => ['required', 'string', 'max:255'],
            'parent_relationship' => ['required', 'string', 'max:100'],
            'parent_phone' => ['required', 'string', 'regex:/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'parent_phone.regex' => 'Enter a valid 10-digit Philippine mobile number, e.g. 917-123-4567.',
        ]);

        $maxReturn = now()->parse($validated['start_date'])->addYear();
        if (now()->parse($validated['return_date'])->gt($maxReturn)) {
            return back()
                ->withInput()
                ->withErrors(['return_date' => 'The approved leave period cannot exceed one year.']);
        }

        DB::transaction(function () use ($loa, $validated, $access, $request) {
            // Normalize phone to E.164-style +63xxxxxxxxxx
            $phone = preg_replace('/[\s\-]/', '', $validated['parent_phone']);
            $phone = ltrim($phone, '0');
            if (! str_starts_with($phone, '+63')) {
                $phone = '+63' . $phone;
            }

            $loa->fill([
                'full_name'           => $validated['full_name'],
                'department_id'       => $validated['department_id'],
                'program_id'          => $validated['program_id'],
                'year_level'          => $validated['year_level'],
                'start_date'          => $validated['start_date'],
                'return_date'         => $validated['return_date'],
                'reason'              => $validated['reason'],
                'parent_full_name'    => $validated['parent_full_name'],
                'parent_relationship' => $validated['parent_relationship'],
                'parent_phone'        => $phone,
            ]);
            $loa->assignControlNumber();
            $loa->status = 'submitted';
            $loa->dept_head_status = ApprovalStageStatus::Pending;
            $loa->submitted_at = now();
            $loa->save();

            foreach ($request->file('attachments', []) as $file) {
                $path = $file->store('loa-attachments/'.$loa->id, 'local');

                // Use server-side finfo detection — never trust the client-supplied MIME type.
                // Sanitize the original filename to prevent path traversal and null bytes.
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

            $access->forceFill(['used_at' => now()])->save();
        });

        // Send confirmation email with form summary to the student.
        try {
            $loa->load(['department', 'program', 'attachments']);
            Mail::to($loa->email)->send(new LoaSubmittedMail($loa));
        } catch (\Throwable $e) {
            report($e);
            // Non-fatal — submission is already recorded, just log the failure.
        }

        return redirect()->route('student.form.submitted', ['token' => $token]);
    }

    public function submitted(string $token): View|RedirectResponse
    {
        $access = $this->resolveToken($token, allowUsed: true);

        if ($access instanceof RedirectResponse) {
            return $access;
        }

        $loa = $access->loaRequest;

        if (! $loa?->isSubmitted()) {
            return redirect()->route('student.form.show', ['token' => $token]);
        }

        $loa->load(['department', 'program']);

        return view('student.submitted', ['loa' => $loa]);
    }

    private function resolveToken(string $token, bool $allowUsed = false): LoaAccessToken|RedirectResponse
    {
        // Guard: token must be exactly 40 alphanumeric chars — anything else is
        // obviously invalid (email client truncation, partial paste, etc.)
        if (! preg_match('/^[A-Za-z0-9]{40}$/', $token)) {
            return redirect()
                ->route('student.identity')
                ->withErrors(['token' => 'That form link appears to be incomplete or was copied incorrectly. Please request a new one.']);
        }

        $access = LoaAccessToken::query()
            ->where('token_hash', LoaAccessToken::hashPlainToken($token))
            ->first();

        if (! $access) {
            return redirect()
                ->route('student.identity')
                ->withErrors(['token' => 'That form link is invalid. It may have been altered or already replaced. Please request a new one.']);
        }

        if ($access->isExpired()) {
            return redirect()
                ->route('student.identity')
                ->withErrors(['token' => 'That form link expired 24 hours after it was sent. Please request a new one.']);
        }

        if (! $allowUsed && $access->used_at && $access->loaRequest?->isSubmitted()) {
            return redirect()->route('student.form.submitted', ['token' => $token]);
        }

        return $access;
    }
}
