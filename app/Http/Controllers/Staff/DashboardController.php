<?php

namespace App\Http\Controllers\Staff;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\LoaRequest;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();

        $loas = LoaRequest::query()
            ->with(['department', 'program', 'deptHeadActor', 'sasoActor', 'campusDirectorActor'])
            ->scopeForUser($user)
            ->latest('submitted_at')
            ->get();

        $stats = [
            'total' => $loas->count(),
            'activeRole' => $user->role->label(),
        ];

        return view('staff.dashboard', compact('user', 'loas', 'stats'));
    }
}
