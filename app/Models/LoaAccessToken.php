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
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
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
}
