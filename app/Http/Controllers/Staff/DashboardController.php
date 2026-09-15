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

        $query = LoaRequest::query()
            ->with(['department', 'program'])
            ->where('status', 'submitted')
            ->latest('submitted_at');

        if ($user->role === UserRole::DepartmentHead) {
            $query->where('department_id', $user->department_id);
        }

        return view('staff.dashboard', [
            'requests' => $query->get(),
        ]);
    }
}
