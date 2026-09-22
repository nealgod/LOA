<?php

namespace App\Models;

use App\Enums\ApprovalStageStatus;
use App\Enums\UserRole;
use App\Mail\LoaApprovedMail;
use App\Mail\LoaRejectedMail;
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
        'registrar_status',
        'registrar_at',
        'registrar_by',
        'guidance_status',
        'guidance_at',
        'guidance_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
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
            'registrar_at'            => 'datetime',
            'guidance_at'             => 'datetime',
            'rejected_at'             => 'datetime',
            'dept_head_status'        => ApprovalStageStatus::class,
            'saso_status'             => ApprovalStageStatus::class,
            'campus_director_status'  => ApprovalStageStatus::class,
            'registrar_status'        => ApprovalStageStatus::class,
            'guidance_status'         => ApprovalStageStatus::class,
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

    public function registrarActor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrar_by');
    }

    public function guidanceActor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guidance_by');
    }

    public function rejectedByActor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
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

        $dh   = $this->dept_head_status;
        $saso = $this->saso_status;
        $cd   = $this->campus_director_status;
        $reg  = $this->registrar_status;
        $guid = $this->guidance_status;

        if ($dh === null || $dh->is(ApprovalStageStatus::Pending))   return 'dept_head';
        if ($dh->is(ApprovalStageStatus::Rejected))                   return 'rejected';

        if ($saso === null || $saso->is(ApprovalStageStatus::Pending)) return 'saso';
        if ($saso->is(ApprovalStageStatus::Rejected))                  return 'rejected';

        if ($cd === null || $cd->is(ApprovalStageStatus::Pending))    return 'campus_director';
        if ($cd->is(ApprovalStageStatus::Rejected))                   return 'rejected';

        if ($reg === null || $reg->is(ApprovalStageStatus::Pending))  return 'registrar';
        if ($reg->is(ApprovalStageStatus::Rejected))                  return 'rejected';

        if ($guid === null || $guid->is(ApprovalStageStatus::Pending)) return 'guidance';
        if ($guid->is(ApprovalStageStatus::Rejected))                  return 'rejected';

        if ($dh->is(ApprovalStageStatus::Approved)
            && $saso->is(ApprovalStageStatus::Approved)
            && $cd->is(ApprovalStageStatus::Approved)
            && $reg->is(ApprovalStageStatus::Approved)
            && $guid->is(ApprovalStageStatus::Approved)) {
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
            'registrar'       => 'Pending: Registrar',
            'guidance'        => 'Pending: Guidance',
            'done'            => 'Fully Approved',
            'rejected'        => 'Rejected',
            default           => 'Unknown',
        };
    }

    public function currentStageStatus(): ?ApprovalStageStatus
    {
        return match ($this->determineCurrentStage()) {
            'dept_head'       => $this->dept_head_status,
            'saso'            => $this->saso_status,
            'campus_director' => $this->campus_director_status,
            'registrar'       => $this->registrar_status,
            'guidance'        => $this->guidance_status,
            default           => null,
        };
    }

    private function roleStageKey(User $user): ?string
    {
        return match ($user->role) {
            UserRole::DepartmentHead  => 'dept_head',
            UserRole::SasoOfficer     => 'saso',
            UserRole::CampusDirector  => 'campus_director',
            UserRole::Registrar       => 'registrar',
            UserRole::Guidance        => 'guidance',
            default                   => null,
        };
    }

    private function priorStagesAllApproved(string $stageKey): bool
    {
        $order = ['dept_head', 'saso', 'campus_director', 'registrar', 'guidance'];
        $idx = array_search($stageKey, $order, true);
        if ($idx === false || $idx === 0) {
            return true;
        }

        for ($i = 0; $i < $idx; $i++) {
            $priorStatus = match ($order[$i]) {
                'dept_head'       => $this->dept_head_status,
                'saso'            => $this->saso_status,
                'campus_director' => $this->campus_director_status,
                'registrar'       => $this->registrar_status,
                'guidance'        => $this->guidance_status,
            };
            if (! $priorStatus || ! $priorStatus->is(ApprovalStageStatus::Approved)) {
                return false;
            }
        }

        return true;
    }

    public function canBeApprovedBy(User $user): bool
    {
        $stageKey = $this->roleStageKey($user);
        if ($stageKey === null) {
            return false;
        }

        $currentStatus = match ($stageKey) {
            'dept_head'       => $this->dept_head_status,
            'saso'            => $this->saso_status,
            'campus_director' => $this->campus_director_status,
            'registrar'       => $this->registrar_status,
            'guidance'        => $this->guidance_status,
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
        $order = ['dept_head', 'saso', 'campus_director', 'registrar', 'guidance'];
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

            // All 5 stages approved → fully done.
            if ($this->dept_head_status?->is(ApprovalStageStatus::Approved)
                && $this->saso_status?->is(ApprovalStageStatus::Approved)
                && $this->campus_director_status?->is(ApprovalStageStatus::Approved)
                && $this->registrar_status?->is(ApprovalStageStatus::Approved)
                && $this->guidance_status?->is(ApprovalStageStatus::Approved)) {
                $this->update(['status' => 'approved']);
                $fullyApproved = true;
            }
        });

        // Send approval email AFTER the transaction commits — not inside it.
        if ($fullyApproved) {
            $this->refresh();
            try {
                $this->load(['department', 'program', 'deptHeadActor', 'sasoActor', 'campusDirectorActor', 'registrarActor', 'guidanceActor']);
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

        $now = now();

        $this->update([
            "{$stageKey}_status" => ApprovalStageStatus::Rejected,
            "{$stageKey}_at"     => $now,
            "{$stageKey}_by"     => $user->id,
            'status'             => 'rejected',
            'rejected_at'        => $now,
            'rejected_by'        => $user->id,
            'rejection_reason'   => $reason,
        ]);

        // Notify student — pipeline terminated.
        try {
            $this->refresh();
            $this->load(['department', 'program', 'rejectedByActor']);
            Mail::to($this->email)->send(new LoaRejectedMail($this));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
