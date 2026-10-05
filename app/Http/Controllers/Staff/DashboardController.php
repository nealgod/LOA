<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\LoaRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = auth()->user();

        $filterStatus = $request->string('status')->toString() ?: null;
        $filterSearch = trim($request->string('search')->toString()) ?: null;

        $validStatuses = ['submitted', 'approved', 'rejected', 'discontinued', 'draft'];
        if ($filterStatus && ! in_array($filterStatus, $validStatuses, true)) {
            $filterStatus = null;
        }

        $loas = LoaRequest::query()
            ->with(['department', 'program', 'deptHeadActor', 'sasoActor', 'campusDirectorActor'])
            ->scopeForUser($user)
            ->when($filterStatus, fn ($q) => $q->where('status', $filterStatus))
            ->when($filterSearch, fn ($q) => $q->where(function ($q2) use ($filterSearch) {
                $q2->where('full_name', 'like', "%{$filterSearch}%")
                   ->orWhere('student_id', 'like', "%{$filterSearch}%")
                   ->orWhere('control_number', 'like', "%{$filterSearch}%");
            }))
            ->latest('submitted_at')
            ->paginate(25)
            ->withQueryString();

        $stats = [
            'total'      => LoaRequest::query()->scopeForUser($user)->count(),
            'activeRole' => $user->role->label(),
        ];

        return view('staff.dashboard', compact('user', 'loas', 'stats', 'filterStatus', 'filterSearch'));
    }
}
