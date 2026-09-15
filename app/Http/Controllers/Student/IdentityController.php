<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Mail\LoaFormAccessMail;
use App\Models\LoaAccessToken;
use App\Models\LoaRequest;
use App\Services\CampusPresenceVerifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class IdentityController extends Controller
{
    public function create(): View
    {
        return view('student.identity');
    }

    public function store(Request $request, CampusPresenceVerifier $verifier): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'string', 'regex:/^\d{4}-\d{4,6}$/'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! str_ends_with(strtolower((string) $value), '@evsu.edu.ph')) {
                        $fail('Personal emails (Gmail, Yahoo, Outlook, and others) are not accepted. Use your official EVSU email ending in @evsu.edu.ph.');
                    }
                },
            ],
        ], [
            'student_id.regex' => 'Student ID must follow the format YYYY-NNNNN, e.g. 2021-12345.',
            'email.email' => 'Enter a valid official EVSU email address.',
        ]);

        $validated['email'] = strtolower($validated['email']);

        if ($this->hasActiveRequest($validated['student_id'], $validated['email'])) {
            return back()
                ->withInput()
                ->withErrors([
                    'student_id' => 'This student number or EVSU email already has an active LOA request. Only one request is allowed at a time.',
                    'email' => 'This student number or EVSU email already has an active LOA request. Only one request is allowed at a time.',
                ]);
        }

        $check = $verifier->verifyStudent(
            $validated['student_id'],
            $validated['full_name'],
            $validated['email'],
        );

        if (! $check['ok']) {
            return back()
                ->withInput()
                ->withErrors(['student_id' => $check['message'] ?? 'Student record could not be verified.']);
        }

        [$accessToken, $plainToken] = LoaAccessToken::issue($validated, $request->ip());
        $formUrl = url('/loa/form/'.$plainToken);

        try {
            Mail::to($validated['email'])->send(new LoaFormAccessMail($accessToken, $formUrl));
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors(['email' => 'We could not send the form link right now. Please try again in a moment.']);
        }

        return redirect()
            ->route('student.identity.sent')
            ->with('email', $validated['email']);
    }

    public function sent(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('email')) {
            return redirect()->route('student.identity');
        }

        return view('student.check-email');
    }

    private function hasActiveRequest(string $studentId, string $email): bool
    {
        $samePerson = function ($query) use ($studentId, $email) {
            $query->where('student_id', $studentId)
                ->orWhereRaw('LOWER(email) = ?', [$email]);
        };

        $openLink = LoaAccessToken::query()
            ->where($samePerson)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->exists();

        $openApplication = LoaRequest::query()
            ->where($samePerson)
            ->blockingNewRequest()
            ->exists();

        return $openLink || $openApplication;
    }
}
