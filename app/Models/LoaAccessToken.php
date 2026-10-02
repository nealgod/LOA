<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class LoaAccessToken extends Model
{
    protected $fillable = [
        'student_id',
        'full_name',
        'email',
        'token_hash',
        'expires_at',
        'used_at',
        'ip_address',
        'otp_hash',
        'otp_expires_at',
        'otp_verified',
    ];

    protected function casts(): array
    {
        return [
            'expires_at'    => 'datetime',
            'used_at'       => 'datetime',
            'otp_expires_at'=> 'datetime',
            'otp_verified'  => 'boolean',
        ];
    }

    public function loaRequest(): HasOne
    {
        return $this->hasOne(LoaRequest::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public static function hashPlainToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    public static function issue(array $identity, ?string $ipAddress = null): array
    {
        // 40 chars = 320 bits of entropy — shorter URLs are less likely to be
        // line-wrapped by email clients, which was causing "invalid link" errors.
        $plainToken = Str::random(40);

        $record = self::create([
            'student_id' => $identity['student_id'],
            'full_name'  => $identity['full_name'],
            'email'      => $identity['email'],
            'token_hash' => self::hashPlainToken($plainToken),
            'expires_at' => now()->addHours(24),
            'ip_address' => $ipAddress,
        ]);

        return [$record, $plainToken];
    }

    /**
     * Issue a pending token for an email address (before OTP is verified).
     * student_id and full_name are left empty — filled in after OTP passes.
     * Returns [record, plainToken, plainOtp].
     */
    public static function issueForEmail(string $email, ?string $ipAddress = null): array
    {
        $plainToken = Str::random(40);
        $plainOtp   = (string) random_int(100000, 999999); // 6-digit OTP

        $record = self::create([
            'student_id'    => '',          // filled after OTP verified
            'full_name'     => '',          // filled after OTP verified
            'email'         => $email,
            'token_hash'    => self::hashPlainToken($plainToken),
            'expires_at'    => now()->addHours(24),
            'ip_address'    => $ipAddress,
            'otp_hash'      => hash('sha256', $plainOtp),
            'otp_expires_at'=> now()->addMinutes(10),
            'otp_verified'  => false,
        ]);

        return [$record, $plainToken, $plainOtp];
    }

    /**
     * Verify the OTP for a pending token.
     * Returns true and marks verified, or false if invalid/expired.
     */
    public function verifyOtp(string $plainOtp): bool
    {
        if ($this->otp_verified) {
            return true; // already verified
        }

        if (! $this->otp_expires_at || $this->otp_expires_at->isPast()) {
            return false; // expired
        }

        if (! hash_equals($this->otp_hash ?? '', hash('sha256', $plainOtp))) {
            return false; // wrong code
        }

        $this->update(['otp_verified' => true]);

        return true;
    }
}
