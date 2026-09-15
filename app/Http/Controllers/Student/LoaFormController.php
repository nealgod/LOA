<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\LoaAccessToken;
use App\Models\LoaRequest;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'programs' => Program::query()->orderBy('name')->get(['id', 'department_id', 'name']),
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
            'start_date' => ['required', 'date'],
            'return_date' => ['required', 'date', 'after:start_date'],
            'reason' => ['required', 'string', 'max:5000'],
            'parent_full_name' => ['required', 'string', 'max:255'],
            'parent_relationship' => ['required', 'string', 'max:100'],
            'parent_phone' => ['required', 'string', 'max:30'],
            'attachments' => ['required', 'array', 'min:1', 'max:10'],
            'attachments.*' => ['file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $maxReturn = now()->parse($validated['start_date'])->addYear();
        if (now()->parse($validated['return_date'])->gt($maxReturn)) {
            return back()
                ->withInput()
                ->withErrors(['return_date' => 'The approved leave period cannot exceed one year.']);
        }

        DB::transaction(function () use ($loa, $validated, $access, $request) {
            $loa->fill([
                'department_id' => $validated['department_id'],
                'program_id' => $validated['program_id'],
                'start_date' => $validated['start_date'],
                'return_date' => $validated['return_date'],
                'reason' => $validated['reason'],
                'parent_full_name' => $validated['parent_full_name'],
                'parent_relationship' => $validated['parent_relationship'],
                'parent_phone' => $validated['parent_phone'],
            ]);
            $loa->assignControlNumber();
            $loa->status = 'submitted';
            $loa->submitted_at = now();
            $loa->save();

            foreach ($request->file('attachments', []) as $file) {
                $path = $file->store('loa-attachments/'.$loa->id, 'local');

                $loa->attachments()->create([
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $file->getClientMimeType() ?: $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }

            $access->forceFill(['used_at' => now()])->save();
        });

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

        return view('student.submitted', ['loa' => $loa]);
    }

    private function resolveToken(string $token, bool $allowUsed = false): LoaAccessToken|RedirectResponse
    {
        $access = LoaAccessToken::query()
            ->where('token_hash', LoaAccessToken::hashPlainToken($token))
            ->first();

        if (! $access) {
            return redirect()
                ->route('student.identity')
                ->withErrors(['token' => 'That form link is invalid. Please request a new one.']);
        }

        if ($access->isExpired()) {
            return redirect()
                ->route('student.identity')
                ->withErrors(['token' => 'That form link has expired. Please request a new one.']);
        }

        if (! $allowUsed && $access->used_at && $access->loaRequest?->isSubmitted()) {
            return redirect()->route('student.form.submitted', ['token' => $token]);
        }

        return $access;
    }
}
