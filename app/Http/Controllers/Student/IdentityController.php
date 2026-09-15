<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Mail\LoaFormAccessMail;
use App\Models\LoaAccessToken;
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
            'student_id' => ['required', 'string', 'max:40'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ], [
            'email.email' => 'Enter a valid official EVSU email address.',
        ]);

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
        $formUrl = route('student.form.show', ['token' => $plainToken]);

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
}
