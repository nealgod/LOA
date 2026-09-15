<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\LoaRequest;
use App\Models\Program;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportsController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();

        // Base query scoped to role (DH sees own dept only)
        $base = LoaRequest::query()->scopeForUser($user);

        // ── Summary counts ────────────────────────────────────────────────────
        $totalCount    = (clone $base)->count();
        $submittedCount = (clone $base)->where('status', 'submitted')->count();
        $approvedCount  = (clone $base)->where('status', 'approved')->count();
        $rejectedCount  = (clone $base)->where('status', 'rejected')->count();

        $approvalRate = $totalCount > 0
            ? round(($approvedCount / $totalCount) * 100)
            : 0;

        // ── Avg processing days (submission → CD approval) ────────────────────
        // Computed in PHP to stay DB-agnostic (SQLite has no DATEDIFF).
        $processed = (clone $base)
            ->whereNotNull('submitted_at')
            ->whereNotNull('campus_director_at')
            ->get(['submitted_at', 'campus_director_at']);

        $avgProcessingDays = $processed->isNotEmpty()
            ? round($processed->avg(fn ($r) => $r->submitted_at->diffInDays($r->campus_director_at)), 1)
            : '—';

        // ── LOAs by department ────────────────────────────────────────────────
        $deptRows = Department::query()
            ->orderBy('code')
            ->get(['id', 'code', 'name'])
            ->map(function ($dept) use ($base) {
                $count = (clone $base)->where('department_id', $dept->id)->count();
                return [
                    'code'  => $dept->code,
                    'name'  => $dept->name,
                    'count' => $count,
                ];
            });

        $maxDeptCount = $deptRows->max('count') ?: 1;

        // ── LOAs by status (for doughnut/bar) ─────────────────────────────────
        $statusCounts = [
            'submitted'    => $submittedCount,
            'approved'     => $approvedCount,
            'rejected'     => $rejectedCount,
            'discontinued' => (clone $base)->where('status', 'discontinued')->count(),
            'draft'        => (clone $base)->where('status', 'draft')->count(),
        ];

        // ── Top 5 programs by volume ──────────────────────────────────────────
        $topPrograms = (clone $base)
            ->select('program_id', DB::raw('COUNT(*) as loa_count'))
            ->whereNotNull('program_id')
            ->groupBy('program_id')
            ->orderByDesc('loa_count')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                $prog = Program::query()->find($row->program_id);
                return [
                    'code'  => $prog?->code ?? '—',
                    'name'  => $prog?->name ?? 'Unknown',
                    'count' => $row->loa_count,
                ];
            });

        $maxProgramCount = $topPrograms->max('count') ?: 1;

        // ── Monthly submissions (last 6 months) ───────────────────────────────
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date  = now()->subMonths($i)->startOfMonth();
            $count = (clone $base)
                ->whereYear('submitted_at', $date->year)
                ->whereMonth('submitted_at', $date->month)
                ->count();
            $months->push([
                'label' => $date->format('M'),
                'count' => $count,
            ]);
        }
        $maxMonthCount = $months->max('count') ?: 1;

        // ── Highest program (for summary card) ────────────────────────────────
        $highestProgram = $topPrograms->first();

        return view('staff.reports', compact(
            'user',
            'totalCount',
            'approvedCount',
            'rejectedCount',
            'submittedCount',
            'approvalRate',
            'avgProcessingDays',
            'deptRows',
            'maxDeptCount',
            'statusCounts',
            'topPrograms',
            'maxProgramCount',
            'months',
            'maxMonthCount',
            'highestProgram',
        ));
    }
}
