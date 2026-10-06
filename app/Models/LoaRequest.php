<?php

namespace App\Models;

use App\Enums\ApprovalStageStatus;
use App\Enums\UserRole;
use App\Mail\LoaApprovedMail;
use App\Mail\LoaRejectedMail;
use App\Models\LoaRejectionHistory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Mail;

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
        'dept_head_status',
        'dept_head_at',
        'dept_head_by',
        'saso_status',
        'saso_at',
        'saso_by',
        'campus_director_status',
        'campus_director_at',
        'campus_director_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
        'discontinued_at',
        'discontinued_by',
        'discontinuation_reason',
        'resubmit_token_hash',
        'resubmit_token_expires_at',
        'resubmit_count',
    ];

    protected function casts(): array
    {
        return [
            'start_date'              => 'date',
            'return_date'             => 'date',
            'submitted_at'            => 'datetime',
            'dept_head_at'            => 'datetime',
            'saso_at'                 => 'datetime',
            'campus_director_at'      => 'datetime',
            'rejected_at'             => 'datetime',
            'discontinued_at'         => 'datetime',
            'resubmit_token_expires_at' => 'datetime',
            'dept_head_status'        => ApprovalStageStatus::class,
            'saso_status'             => ApprovalStageStatus::class,
            'campus_director_status'  => ApprovalStageStatus::class,
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

    public function deptHeadActor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dept_head_by');
    }

    public function sasoActor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'saso_by');
    }

    public function campusDirectorActor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'campus_director_by');
    }

    public function rejectedByActor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function discontinuedByActor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'discontinued_by');
    }

    public function rejectionHistory(): HasMany
    {
        return $this->hasMany(LoaRejectionHistory::class)->orderBy('rejected_at');
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

    public function scopeScopeForUser(Builder $query, User $user): Builder
    {
        if ($user->role->is(UserRole::DepartmentHead)) {
            return $query->where('department_id', $user->department_id);
        }

        return $query;
    }

    public function statusLabel(): string
    {
        return match (strtolower($this->status)) {
            'submitted' => 'Submitted',
            'rejected' => 'Rejected',
            'discontinued' => 'Withdrawn / Discontinued',
            'draft' => 'Draft',
            default => ucfirst(str_replace(['_', '-'], ' ', $this->status ?? 'unknown')),
        };
    }

    public function statusBadgeClasses(): string
    {
        return match (strtolower($this->status)) {
            'submitted' => 'border border-maroon-600 text-maroon-800 bg-maroon-50 rounded-full px-3 py-1 text-xs font-medium',
            'rejected' => 'border border-red-600 text-white bg-red-600 rounded-full px-3 py-1 text-xs font-medium',
            'discontinued' => 'border border-gray-500 text-white bg-gray-500 rounded-full px-3 py-1 text-xs font-medium',
            'draft' => 'border border-yellow-500 text-yellow-800 bg-yellow-50 rounded-full px-3 py-1 text-xs font-medium',
            default => 'border border-gray-600 text-gray-800 bg-gray-50 rounded-full px-3 py-1 text-xs font-medium',
        };
    }

    public function assignControlNumber(): void
    {
        if ($this->control_number) {
            return;
        }

        $year = now()->year;
        $sequence = static::query()
            ->whereNotNull('control_number')
            ->where('control_number', 'like', "EVSU-OC-LOA-{$year}-%")
            ->count() + 1;

        $this->control_number = sprintf('EVSU-OC-LOA-%d-%04d', $year, $sequence);
    }

    public function determineCurrentStage(): string
    {
        if ($this->status === 'rejected') {
            return 'rejected';
        }

        if ($this->status === 'discontinued') {
            return 'discontinued';
        }

        $dh   = $this->dept_head_status;
        $saso = $this->saso_status;
        $cd   = $this->campus_director_status;

        if ($dh === null || $dh->is(ApprovalStageStatus::Pending))   return 'dept_head';
        if ($dh->is(ApprovalStageStatus::Rejected))                   return 'rejected';

        if ($saso === null || $saso->is(ApprovalStageStatus::Pending)) return 'saso';
        if ($saso->is(ApprovalStageStatus::Rejected))                  return 'rejected';

        if ($cd === null || $cd->is(ApprovalStageStatus::Pending))    return 'campus_director';
        if ($cd->is(ApprovalStageStatus::Rejected))                   return 'rejected';

        if ($dh->is(ApprovalStageStatus::Approved)
            && $saso->is(ApprovalStageStatus::Approved)
            && $cd->is(ApprovalStageStatus::Approved)) {
            return 'done';
        }

        return 'unknown';
    }

    public function currentStageLabel(): string
    {
        return match ($this->determineCurrentStage()) {
            'dept_head'       => 'Pending: Dept Head',
            'saso'            => 'Pending: SASO',
            'campus_director' => 'Pending: Campus Director',
            'done'            => 'Fully Approved',
            'rejected'        => 'Rejected',
            'discontinued'    => 'Withdrawn / Discontinued',
            default           => 'Unknown',
        };
    }

    public function currentStageStatus(): ?ApprovalStageStatus
    {
        return match ($this->determineCurrentStage()) {
            'dept_head'       => $this->dept_head_status,
            'saso'            => $this->saso_status,
            'campus_director' => $this->campus_director_status,
            default           => null,
        };
    }

    private function roleStageKey(User $user): ?string
    {
        // Registrar and Guidance are view-only — they have no approval stage key.
        return match ($user->role) {
            UserRole::DepartmentHead  => 'dept_head',
            UserRole::SasoOfficer     => 'saso',
            UserRole::CampusDirector  => 'campus_director',
            default                   => null,
        };
    }

    private function priorStagesAllApproved(string $stageKey): bool
    {
        $order = ['dept_head', 'saso', 'campus_director'];
        $idx = array_search($stageKey, $order, true);
        if ($idx === false || $idx === 0) {
            return true;
        }

        for ($i = 0; $i < $idx; $i++) {
            $priorStatus = match ($order[$i]) {
                'dept_head'       => $this->dept_head_status,
                'saso'            => $this->saso_status,
                'campus_director' => $this->campus_director_status,
            };
            if (! $priorStatus || ! $priorStatus->is(ApprovalStageStatus::Approved)) {
                return false;
            }
        }

        return true;
    }

    public function canBeApprovedBy(User $user): bool
    {
        if ($this->status !== 'submitted') {
            return false;
        }

        $stageKey = $this->roleStageKey($user);
        if ($stageKey === null) {
            return false;
        }

        $currentStatus = match ($stageKey) {
            'dept_head'       => $this->dept_head_status,
            'saso'            => $this->saso_status,
            'campus_director' => $this->campus_director_status,
        };

        if (! $currentStatus || ! $currentStatus->is(ApprovalStageStatus::Pending)) {
            return false;
        }

        return $this->priorStagesAllApproved($stageKey);
    }

    public function canBeRejectedBy(User $user): bool
    {
        return $this->canBeApprovedBy($user);
    }

    public function markApprovedBy(User $user): void
    {
        $stageKey = $this->roleStageKey($user);
        if ($stageKey === null) {
            return;
        }

        $now   = now();
        $order = ['dept_head', 'saso', 'campus_director'];
        $idx   = array_search($stageKey, $order, true);

        $fullyApproved = false;

        \Illuminate\Support\Facades\DB::transaction(function () use ($stageKey, $user, $now, $order, $idx, &$fullyApproved) {
            $changes = [
                "{$stageKey}_status" => ApprovalStageStatus::Approved,
                "{$stageKey}_at"     => $now,
                "{$stageKey}_by"     => $user->id,
            ];

            // Unlock the next stage in the same atomic write.
            if ($idx !== false && $idx < count($order) - 1) {
                $nextStage = $order[$idx + 1];
                $changes["{$nextStage}_status"] = ApprovalStageStatus::Pending;
            }

            $this->update($changes);
            $this->refresh();

            // All 3 stages approved → fully done.
            if ($this->dept_head_status?->is(ApprovalStageStatus::Approved)
                && $this->saso_status?->is(ApprovalStageStatus::Approved)
                && $this->campus_director_status?->is(ApprovalStageStatus::Approved)) {
                $this->update(['status' => 'approved']);
                $fullyApproved = true;
            }
        });

        // Send approval email AFTER the transaction commits — not inside it.
        if ($fullyApproved) {
            $this->refresh();
            try {
                $this->load(['department', 'program', 'deptHeadActor', 'sasoActor', 'campusDirectorActor']);
                Mail::to($this->email)->send(new LoaApprovedMail($this));
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    public function markRejectedBy(User $user, ?string $reason = null): void
    {
        $stageKey = $this->roleStageKey($user);
        if ($stageKey === null) {
            return;
        }

        $now        = now();
        $plainToken = null;

        \Illuminate\Support\Facades\DB::transaction(function () use ($stageKey, $user, $now, $reason, &$plainToken) {
            $this->update([
                "{$stageKey}_status" => ApprovalStageStatus::Rejected,
                "{$stageKey}_at"     => $now,
                "{$stageKey}_by"     => $user->id,
                'status'             => 'rejected',
                'rejected_at'        => $now,
                'rejected_by'        => $user->id,
                'rejection_reason'   => $reason,
            ]);

            // Record this rejection in the history table.
            $stageLabels = [
                'dept_head'       => 'Department Head',
                'saso'            => 'SASO Officer',
                'campus_director' => 'Campus Director',
            ];
            LoaRejectionHistory::create([
                'loa_request_id' => $this->id,
                'stage_key'      => $stageKey,
                'stage_label'    => $stageLabels[$stageKey] ?? ucfirst($stageKey),
                'rejected_by'    => $user->id,
                'rejected_at'    => $now,
                'reason'         => $reason,
                'resubmit_cycle' => $this->resubmit_count,
            ]);

            // Issue the 7-day resubmit token atomically with the rejection write.
            $plainToken = $this->issueResubmitToken();
        });

        // Send rejection email AFTER the transaction commits.
        try {
            $this->refresh();
            $this->load(['department', 'program', 'rejectedByActor']);
            $resubmitUrl = $plainToken ? url('/loa/resubmit/' . $plainToken) : null;
            Mail::to($this->email)->send(new LoaRejectedMail($this, $resubmitUrl));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    // ── Discontinuation ───────────────────────────────────────────────────────

    /**
     * A LOA can be discontinued if it is submitted (before full approval)
     * or approved (student decided not to leave / did not depart).
     * Rejected and already-discontinued LOAs cannot be discontinued again.
     */
    public function canBeDiscontinuedBy(User $user): bool
    {
        if (! in_array($this->status, ['submitted', 'approved'], true)) {
            return false;
        }

        return match ($user->role) {
            UserRole::Administrator,
            UserRole::SasoOfficer,
            UserRole::CampusDirector,
            UserRole::Registrar    => true,
            UserRole::DepartmentHead => $user->department_id
                && (int) $user->department_id === (int) $this->department_id,
            default => false,
        };
    }

    public function markDiscontinued(User $user, ?string $reason = null): void
    {
        $now = now();

        $this->update([
            'status'                => 'discontinued',
            'discontinued_at'       => $now,
            'discontinued_by'       => $user->id,
            'discontinuation_reason'=> $reason,
        ]);
    }

    // ── Resubmission ──────────────────────────────────────────────────────────
    /**
     * Issue a 7-day single-use resubmit token.
     * Returns the plain token (to embed in the email URL).
     */
    public function issueResubmitToken(): string
    {
        $plain = \Illuminate\Support\Str::random(40);

        $this->update([
            'resubmit_token_hash'       => hash('sha256', $plain),
            'resubmit_token_expires_at' => now()->addDays(7),
        ]);

        return $plain;
    }

    /**
     * Find a rejected LOA by its plain resubmit token.
     * Returns null if the token is invalid, expired, or the LOA is not rejected.
     */
    public static function findByResubmitToken(string $plain): ?self
    {
        return self::query()
            ->where('resubmit_token_hash', hash('sha256', $plain))
            ->where('resubmit_token_expires_at', '>', now())
            ->where('status', 'rejected')
            ->first();
    }

    /**
     * Reset the LOA for resubmission.
     * Smart resume: preserves stages that were already approved before the rejection.
     * Only the rejecting stage and later stages are reset.
     * Increments the control number suffix (.1, .2 …).
     */
    public function resubmit(array $fields): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($fields) {
            // Refresh to ensure we read the latest rejection fields.
            $this->refresh();

            // Determine which stage was rejected so we can resume from there.
            // The rejection_reason/rejected_by fields still hold the last rejection at this point.
            $rejectedStageKey = $this->rejectionHistory()
                ->where('resubmit_cycle', $this->resubmit_count)
                ->orderByDesc('rejected_at')
                ->value('stage_key');

            $order = ['dept_head', 'saso', 'campus_director'];
            $rejectedIdx = $rejectedStageKey ? array_search($rejectedStageKey, $order, true) : 0;

            if (! $rejectedStageKey) {
                // No history row found — should never happen. Log it and fall back to full reset.
                report(new \RuntimeException("resubmit(): no history row for loa_request_id={$this->id} cycle={$this->resubmit_count}"));
            }

            // Build stage reset — keep stages before the rejected one, reset from it onward.
            $stageChanges = [];
            foreach ($order as $idx => $key) {
                if ($idx < $rejectedIdx) {
                    // Stage was approved before this rejection — keep it approved.
                    // (no change needed — it is already approved in the DB)
                } elseif ($idx === $rejectedIdx) {
                    // Restart from the rejecting stage — set to pending.
                    $stageChanges["{$key}_status"] = ApprovalStageStatus::Pending;
                    $stageChanges["{$key}_at"]     = null;
                    $stageChanges["{$key}_by"]     = null;
                } else {
                    // Later stages — clear them.
                    $stageChanges["{$key}_status"] = null;
                    $stageChanges["{$key}_at"]     = null;
                    $stageChanges["{$key}_by"]     = null;
                }
            }

            $this->update(array_merge([
                // Increment control number suffix: 0001 → 0001.1 → 0001.2
                'control_number'    => $this->nextControlNumber(),
                // Updated fields from student
                'department_id'     => $fields['department_id'] ?? $this->department_id,
                'program_id'        => $fields['program_id']    ?? $this->program_id,
                'year_level'        => $fields['year_level']    ?? $this->year_level,
                'reason'            => $fields['reason'],
                'start_date'        => $fields['start_date'],
                'return_date'       => $fields['return_date'],
                'parent_full_name'  => $fields['parent_full_name'],
                'parent_relationship' => $fields['parent_relationship'],
                'parent_phone'      => $fields['parent_phone'],
                // Reset overall status
                'status'            => 'submitted',
                'submitted_at'      => now(),
                // Clear rejection fields
                'rejected_at'       => null,
                'rejected_by'       => null,
                'rejection_reason'  => null,
                // Clear resubmit token (consumed)
                'resubmit_token_hash'       => null,
                'resubmit_token_expires_at' => null,
                'resubmit_count'            => $this->resubmit_count + 1,
            ], $stageChanges));
        });
    }

    /**
     * Derive the next control number by incrementing the decimal suffix.
     * EVSU-OC-LOA-2026-0001     → EVSU-OC-LOA-2026-0001.1
     * EVSU-OC-LOA-2026-0001.1   → EVSU-OC-LOA-2026-0001.2
     * EVSU-OC-LOA-2026-0001.9   → EVSU-OC-LOA-2026-0001.10
     */
    private function nextControlNumber(): string
    {
        $current = $this->control_number ?? '';

        if ($current === '') {
            // Should never happen — control_number is always assigned on submission.
            report(new \RuntimeException("resubmit() called on LoaRequest #{$this->id} with null control_number"));
            return 'UNKNOWN.1';
        }

        if (str_contains($current, '.')) {
            [$base, $rev] = explode('.', $current, 2);
            return $base . '.' . ((int) $rev + 1);
        }

        return $current . '.1';
    }
}
