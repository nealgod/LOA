<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoaRequest extends Model
{
    protected $fillable = [
        'control_number',
        'loa_access_token_id',
        'student_id',
        'full_name',
        'email',
        'department_id',
        'program_id',
        'year_level',
        'start_date',
        'return_date',
        'reason',
        'parent_full_name',
        'parent_relationship',
        'parent_phone',
        'status',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'return_date' => 'date',
            'submitted_at' => 'datetime',
        ];
    }

    public function accessToken(): BelongsTo
    {
        return $this->belongsTo(LoaAccessToken::class, 'loa_access_token_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(LoaAttachment::class);
    }

    public function isSubmitted(): bool
    {
        return $this->status !== 'draft' && filled($this->control_number);
    }

    public function scopeBlockingNewRequest($query)
    {
        return $query
            ->whereNotIn('status', ['rejected', 'discontinued'])
            ->where(function ($q) {
                $q->where('status', '!=', 'draft')
                    ->orWhereHas('accessToken', function ($token) {
                        $token->where('expires_at', '>', now())->whereNull('used_at');
                    });
            });
    }

    public function assignControlNumber(): void
    {
        if ($this->control_number) {
            return;
        }

        $year = now()->year;
        $sequence = static::query()
            ->whereNotNull('control_number')
            ->where('control_number', 'like', "EVSU-OR-LOA-{$year}-%")
            ->count() + 1;

        $this->control_number = sprintf('EVSU-OR-LOA-%d-%04d', $year, $sequence);
    }
}
