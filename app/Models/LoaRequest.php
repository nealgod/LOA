<?php

namespace App\Models;

use App\Enums\ApprovalStageStatus;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
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
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'return_date' => 'date',
            'submitted_at' => 'datetime',
            'dept_head_at' => 'datetime',
            'saso_at' => 'datetime',
            'campus_director_at' => 'datetime',
            'rejected_at' => 'datetime',
            'dept_head_status' => ApprovalStageStatus::class,
            'saso_status' => ApprovalStageStatus::class,
            'campus_director_status' => ApprovalStageStatus::class,
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

        $dh = $this->dept_head_status;
        $saso = $this->saso_status;
        $cd = $this->campus_director_status;

        if ($dh === null || $dh->is(ApprovalStageStatus::Pending)) {
            return 'dept_head';
        }

        if ($dh->is(ApprovalStageStatus::Rejected)) {
            return 'rejected';
        }

        if ($saso === null || $saso->is(ApprovalStageStatus::Pending)) {
            return 'saso';
        }

        if ($saso->is(ApprovalStageStatus::Rejected)) {
            return 'rejected';
        }

        if ($cd === null || $cd->is(ApprovalStageStatus::Pending)) {
            return 'campus_director';
        }

        if ($cd->is(ApprovalStageStatus::Rejected)) {
            return 'rejected';
        }

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
            'dept_head' => 'Pending: Dept Head',
            'saso' => 'Pending: SASO',
            'campus_director' => 'Pending: Campus Director',
            'done' => 'Fully Approved',
            'rejected' => 'Rejected',
            default => 'Unknown',
        };
    }

    public function currentStageStatus(): ?ApprovalStageStatus
    {
        $stage = $this->determineCurrentStage();

        return match ($stage) {
            'dept_head' => $this->dept_head_status,
            'saso' => $this->saso_status,
            'campus_director' => $this->campus_director_status,
            default => null,
        };
    }

    private function roleStageKey(User $user): ?string
    {
        return match ($user->role) {
            UserRole::DepartmentHead => 'dept_head',
            UserRole::SasoOfficer => 'saso',
            UserRole::CampusDirector => 'campus_director',
            default => null,
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
                'dept_head' => $this->dept_head_status,
                'saso' => $this->saso_status,
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
        $stageKey = $this->roleStageKey($user);
        if ($stageKey === null) {
            return false;
        }

        $currentStatus = match ($stageKey) {
            'dept_head' => $this->dept_head_status,
            'saso' => $this->saso_status,
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

        $now = now();

        \Illuminate\Support\Facades\DB::transaction(function () use ($stageKey, $user, $now) {
            $order = ['dept_head', 'saso', 'campus_director'];
            $idx   = array_search($stageKey, $order, true);

            // Build all column changes in a single update to avoid partial writes.
            $changes = [
                "{$stageKey}_status" => ApprovalStageStatus::Approved,
                "{$stageKey}_at"     => $now,
                "{$stageKey}_by"     => $user->id,
            ];

            // Unlock the next stage in the same atomic update.
            if ($idx !== false && $idx < count($order) - 1) {
                $nextStage = $order[$idx + 1];
                $changes["{$nextStage}_status"] = ApprovalStageStatus::Pending;
            }

            $this->update($changes);
            $this->refresh();

            // If all three stages are approved, mark the overall LOA as approved.
            if ($this->dept_head_status?->is(ApprovalStageStatus::Approved)
                && $this->saso_status?->is(ApprovalStageStatus::Approved)
                && $this->campus_director_status?->is(ApprovalStageStatus::Approved)) {
                $this->update(['status' => 'approved']);
            }
        });
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
            "{$stageKey}_at" => $now,
            "{$stageKey}_by" => $user->id,
            'status' => 'rejected',
            'rejected_at' => $now,
            'rejected_by' => $user->id,
            'rejection_reason' => $reason,
        ]);
    }
}
