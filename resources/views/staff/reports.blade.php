@extends('layouts.staff')

@section('eyebrow', 'Reports & Analytics')
@section('title', 'Reports & Analytics')

@section('content')

    {{-- ── Filter labels (retained for consistency) ───────────────────────── --}}
    <div class="mb-6 flex flex-wrap gap-4 rounded-2xl border border-maroon-900/10 bg-white px-5 py-4 shadow-sm">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-maroon-900/50 mb-1">Semester</p>
            <select disabled class="rounded-lg border border-maroon-900/15 bg-cream-50 px-3 py-2 text-sm text-maroon-950 opacity-60 cursor-not-allowed">
                <option>All Semesters ({{ now()->year }})</option>
            </select>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-maroon-900/50 mb-1">Program / Department</p>
            <select disabled class="rounded-lg border border-maroon-900/15 bg-cream-50 px-3 py-2 text-sm text-maroon-950 opacity-60 cursor-not-allowed">
                <option>All Departments</option>
            </select>
        </div>
        <p class="self-end text-xs text-maroon-900/40 italic">Live filters coming in a future release.</p>
    </div>

    {{-- ── Summary stat cards ──────────────────────────────────────────────── --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-2xl border border-maroon-900/10 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-maroon-900/60">LOA Count</p>
            <p class="mt-2 text-4xl font-bold tracking-tight text-maroon-950">{{ $totalCount }}</p>
            <p class="mt-1 text-xs text-maroon-900/50">Total in scope</p>
        </div>
        <div class="rounded-2xl border border-maroon-900/10 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-maroon-900/60">Approval Rate</p>
            <p class="mt-2 text-4xl font-bold tracking-tight text-emerald-700">{{ $approvalRate }}<span class="text-xl">%</span></p>
            <p class="mt-1 text-xs text-maroon-900/50">{{ $approvedCount }} approved / {{ $totalCount }} total</p>
        </div>
        <div class="rounded-2xl border border-maroon-900/10 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-maroon-900/60">Processing Time</p>
            <p class="mt-2 text-4xl font-bold tracking-tight text-maroon-950">
                {{ $avgProcessingDays }}<span class="text-xl font-medium text-maroon-900/60"> days</span>
            </p>
            <p class="mt-1 text-xs text-maroon-900/50">Avg submission → CD approval</p>
        </div>
        <div class="rounded-2xl border border-maroon-900/10 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-maroon-900/60">Highest Program</p>
            @if ($highestProgram)
                <p class="mt-2 text-2xl font-bold tracking-tight text-maroon-950 truncate" title="{{ $highestProgram['name'] }}">
                    {{ $highestProgram['code'] }}
                </p>
                <p class="mt-1 text-xs text-maroon-900/50">{{ $highestProgram['count'] }} submissions</p>
            @else
                <p class="mt-2 text-2xl font-bold tracking-tight text-maroon-900/30">—</p>
                <p class="mt-1 text-xs text-maroon-900/50">No data yet</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- ── LOA Volume by Department (real bar chart) ───────────────────── --}}
        <div class="rounded-2xl border border-maroon-900/10 bg-white p-6 shadow-sm">
            <h3 class="mb-5 text-sm font-semibold text-maroon-950">Volume by Department</h3>
            @if ($deptRows->sum('count') === 0)
                <p class="py-8 text-center text-sm text-maroon-900/40">No submissions yet.</p>
            @else
                <div class="space-y-3">
                    @foreach ($deptRows as $dept)
                        @php $pct = $maxDeptCount > 0 ? round(($dept['count'] / $maxDeptCount) * 100) : 0; @endphp
                        <div>
                            <div class="mb-1 flex items-center justify-between text-xs">
                                <span class="font-semibold text-maroon-950">{{ $dept['code'] }}</span>
                                <span class="text-maroon-900/60">{{ $dept['count'] }}</span>
                            </div>
                            <div class="h-3 w-full overflow-hidden rounded-full bg-maroon-900/8">
                                <div class="h-full rounded-full bg-maroon-700 transition-all duration-500"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                            <p class="mt-0.5 text-[11px] text-maroon-900/40 truncate">{{ $dept['name'] }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ── Monthly submission trend (last 6 months) ───────────────────── --}}
        <div class="rounded-2xl border border-maroon-900/10 bg-white p-6 shadow-sm">
            <h3 class="mb-5 text-sm font-semibold text-maroon-950">Duration Trend — Monthly Submissions</h3>
            @if ($months->sum('count') === 0)
                <p class="py-8 text-center text-sm text-maroon-900/40">No submissions in the last 6 months.</p>
            @else
                <div class="flex items-end gap-2 h-40 border-b border-l border-maroon-900/10 px-2 pt-2">
                    @foreach ($months as $month)
                        @php $h = $maxMonthCount > 0 ? max(4, round(($month['count'] / $maxMonthCount) * 100)) : 4; @endphp
                        <div class="flex flex-1 flex-col items-center gap-1">
                            <span class="text-[10px] font-semibold text-maroon-900/60">
                                {{ $month['count'] > 0 ? $month['count'] : '' }}
                            </span>
                            <div class="w-full rounded-t-md bg-maroon-600/75 transition-all duration-500"
                                 style="height: {{ $h }}%"></div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-2 flex gap-2 px-2">
                    @foreach ($months as $month)
                        <p class="flex-1 text-center text-[10px] font-semibold text-maroon-900/50">{{ $month['label'] }}</p>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ── Status breakdown ────────────────────────────────────────────── --}}
        <div class="rounded-2xl border border-maroon-900/10 bg-white p-6 shadow-sm">
            <h3 class="mb-5 text-sm font-semibold text-maroon-950">Reasons Breakdown — Status Distribution</h3>
            @if ($totalCount === 0)
                <p class="py-8 text-center text-sm text-maroon-900/40">No data yet.</p>
            @else
                @php
                    $statusDef = [
                        'submitted'    => ['label' => 'Under Review',  'color' => 'bg-maroon-600'],
                        'approved'     => ['label' => 'Approved',       'color' => 'bg-emerald-500'],
                        'rejected'     => ['label' => 'Rejected',       'color' => 'bg-red-500'],
                        'discontinued' => ['label' => 'Discontinued',   'color' => 'bg-gray-400'],
                        'draft'        => ['label' => 'Draft',          'color' => 'bg-yellow-400'],
                    ];
                @endphp
                <div class="space-y-3">
                    @foreach ($statusCounts as $key => $count)
                        @php
                            $def = $statusDef[$key] ?? ['label' => ucfirst($key), 'color' => 'bg-gray-400'];
                            $pct = $totalCount > 0 ? round(($count / $totalCount) * 100) : 0;
                        @endphp
                        <div>
                            <div class="mb-1 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-1.5">
                                    <span class="h-2.5 w-2.5 rounded-full {{ $def['color'] }}"></span>
                                    <span class="font-medium text-maroon-950">{{ $def['label'] }}</span>
                                </div>
                                <span class="text-maroon-900/60">{{ $count }} <span class="text-maroon-900/40">({{ $pct }}%)</span></span>
                            </div>
                            <div class="h-2.5 w-full overflow-hidden rounded-full bg-maroon-900/8">
                                <div class="h-full rounded-full {{ $def['color'] }} transition-all duration-500"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ── Top programs ─────────────────────────────────────────────────── --}}
        <div class="rounded-2xl border border-maroon-900/10 bg-white p-6 shadow-sm">
            <h3 class="mb-5 text-sm font-semibold text-maroon-950">Top Programs by Submission Volume</h3>
            @if ($topPrograms->isEmpty())
                <p class="py-8 text-center text-sm text-maroon-900/40">No data yet.</p>
            @else
                <div class="space-y-3">
                    @foreach ($topPrograms as $i => $prog)
                        @php $pct = $maxProgramCount > 0 ? round(($prog['count'] / $maxProgramCount) * 100) : 0; @endphp
                        <div>
                            <div class="mb-1 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-1.5">
                                    <span class="flex h-4 w-4 items-center justify-center rounded-full bg-maroon-800 text-[9px] font-bold text-white">{{ $i + 1 }}</span>
                                    <span class="font-semibold text-maroon-950">{{ $prog['code'] }}</span>
                                    <span class="hidden text-maroon-900/50 sm:inline truncate max-w-[150px]">{{ $prog['name'] }}</span>
                                </div>
                                <span class="font-semibold text-maroon-900/70">{{ $prog['count'] }}</span>
                            </div>
                            <div class="h-2.5 w-full overflow-hidden rounded-full bg-maroon-900/8">
                                <div class="h-full rounded-full bg-gold-500 transition-all duration-500"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
