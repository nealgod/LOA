<?php

namespace App\Enums;

enum ApprovalStageStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'border border-amber-400 text-amber-800 bg-amber-50 rounded-full px-3 py-1 text-xs font-medium',
            self::Approved => 'border border-emerald-600 text-white bg-emerald-600 rounded-full px-3 py-1 text-xs font-medium',
            self::Rejected => 'border border-red-600 text-white bg-red-600 rounded-full px-3 py-1 text-xs font-medium',
        };
    }

    public function is(self $other): bool
    {
        return $this === $other;
    }
}
