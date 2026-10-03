<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Mail\LoaFormAccessMail;
use App\Mail\LoaOtpMail;
use App\Models\LoaAccessToken;
use App\Models\LoaRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class IdentityController extends Controller
{
    // ── Step 1: show email input ──────────────────────────────────────────────

    public function create(): View
    {
        return view('student.identity');
    }

    // ── Step 2: validate email, send OTP ─────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! str_ends_with(strtolower((string) $value), '@evsu.edu.ph')) {
                        $fail('Only official EVSU email addresses ending in @evsu.edu.ph are accepted.');
                    }
                },
            ],
        ], [
            'email.required' => 'Please enter your EVSU email address.',
            'email.email'    => 'Enter a valid email address.',
        ]);

        $email = strtolower($request->input('email'));

        // Check for existing active request on this email
        if ($this->hasActiveRequestByEmail($email)) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'This EVSU email already has an active LOA request. Only one request is allowed at a time.']);
        }

        // Issue a pending token + 6-digit OTP
        [$accessToken, $plainToken, $plainOtp] = LoaAccessToken::issueForEmail($email, $request->ip());

        try {
            Mail::to($email)->send(new LoaOtpMail($plainOtp, $email));
        } catch (\Throwable $e) {
            report($e);
            // Clean up the token since email failed
            $accessToken->delete();

            return back()
                ->withInput()
                ->withErrors(['email' => 'We could not send the verification code right now. Please try again in a moment.']);
        }

        return redirect()
            ->route('student.identity.verify', ['token' => $plainToken])
            ->with('otp_email', $email);
    }

    // ── Step 3: show OTP entry form ───────────────────────────────────────────

    public function showVerify(Request $request, string $token): View|RedirectResponse
    {
        $accessToken = $this->resolveToken($token);

        if (! $accessToken) {
            return redirect()
                ->route('student.identity')
                ->withErrors(['email' => 'This verification link is invalid or has expired. Please start again.']);
        }

        // Already verified — go to check-email (form link was already sent)
        if ($accessToken->otp_verified) {
            return redirect()
                ->route('student.identity.sent')
                ->with('email', $accessToken->email);
        }

        return view('student.verify-otp', [
            'token' => $token,
            'email' => $accessToken->email,
        ]);
    }

    // ── Step 4: verify OTP, send form link email, show check-email page ──────

    public function verify(Request $request, string $token): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'digits:6'],
        ], [
            'otp.required' => 'Please enter the 6-digit code from your email.',
            'otp.digits'   => 'The code must be exactly 6 digits.',
        ]);

        $accessToken = $this->resolveToken($token);

        if (! $accessToken) {
            return redirect()
                ->route('student.identity')
                ->withErrors(['email' => 'This verification link is invalid or has expired. Please start again.']);
        }

        if ($accessToken->otp_verified) {
            // Already verified — send them to check-email (form link was already sent)
            return redirect()
                ->route('student.identity.sent')
                ->with('email', $accessToken->email);
        }

        if (! $accessToken->verifyOtp($request->input('otp'))) {
            return back()
                ->withErrors(['otp' => 'The code is incorrect or has expired. Check your email and try again.']);
        }

        // OTP correct — send the form link to their email
        $formUrl = url('/loa/form/' . $token);

        try {
            Mail::to($accessToken->email)->send(new LoaFormAccessMail($accessToken, $formUrl));
        } catch (\Throwable $e) {
            report($e);
            return back()
                ->withErrors(['otp' => 'Email verified but we could not send your form link. Please try again.']);
        }

        // Show "check your email" page — form URL is only in the email
        return redirect()
            ->route('student.identity.sent')
            ->with('email', $accessToken->email);
    }

    // ── "Check your email" page — shown after OTP is verified and form link is sent ──

    public function sent(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('email')) {
            return redirect()->route('student.identity');
        }

        return view('student.check-email');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function resolveToken(string $plainToken): ?LoaAccessToken
    {
        $hash = LoaAccessToken::hashPlainToken($plainToken);

        return LoaAccessToken::query()
            ->where('token_hash', $hash)
            ->where('expires_at', '>', now())
            ->whereNull('used_at')
            ->first();
    }

    private function hasActiveRequestByEmail(string $email): bool
    {
        $openLink = LoaAccessToken::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->where('otp_verified', true)   // only block if they already verified
            ->exists();

        $openApplication = LoaRequest::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->blockingNewRequest()
            ->exists();

        return $openLink || $openApplication;
    }
}
