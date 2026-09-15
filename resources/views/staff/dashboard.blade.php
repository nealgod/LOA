@extends('layouts.staff')

@section('eyebrow', 'Dashboard Overview')
@section('title', 'Dashboard Overview')

@section('content')
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2">
        <div class="rounded-2xl border border-maroon-900/10 bg-white p-6 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-maroon-900/60">Total Applications</p>
            <p class="mt-2 text-4xl font-bold tracking-tight text-maroon-950">{{ $stats['total'] }}</p>
            <p class="mt-1 text-xs text-maroon-900/50">In your current scope</p>
        </div>

        <div class="rounded-2xl border border-maroon-900/10 bg-white p-6 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-maroon-900/60">Active Role</p>
            <p class="mt-2 text-3xl font-bold tracking-tight text-maroon-700">{{ $stats['activeRole'] }}</p>
            @if (optional($user->department)->name)
                <p class="mt-1 text-xs text-maroon-900/50">{{ $user->department->name }}</p>
            @endif
        </div>
    </div>

    <div class="mb-3 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-maroon-950">LOA Submissions Overview</h2>
    </div>

    @if ($loas->isEmpty())
        <div class="rounded-2xl border border-dashed border-maroon-900/15 bg-white p-12 text-center">
            <p class="text-sm text-maroon-900/60">No LOA submissions in your current scope yet.</p>
        </div>
    @else
        <div class="overflow-x-auto rounded-2xl border border-maroon-900/10 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-maroon-900/10">
                <thead class="bg-maroon-950/5">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-6">App ID</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-6">Student ID</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-6">Student Name</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-6">Date Effective</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-6">Return Date</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-6">Approval Stage / Status</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-6">Actions / Management</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-maroon-900/10 bg-white">
                    @foreach ($loas as $loa)
                        @php
                            $stage = $loa->currentStageStatus();
                            $stageLabel = $loa->currentStageLabel();
                            $stageClasses = $stage ? $stage->badgeClasses() : match ($loa->determineCurrentStage()) {
                                'done' => 'border border-emerald-600 text-white bg-emerald-600 rounded-full px-3 py-1 text-xs font-medium',
                                'rejected' => 'border border-red-600 text-white bg-red-600 rounded-full px-3 py-1 text-xs font-medium',
                                default => 'border border-gray-400 text-gray-700 bg-gray-50 rounded-full px-3 py-1 text-xs font-medium',
                            };
                        @endphp
                        <tr class="hover:bg-maroon-950/[0.02]">
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-sm font-bold text-maroon-900 sm:px-6">
                                {{ $loa->control_number }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-maroon-900/85 sm:px-6">
                                {{ $loa->student_id ?? '—' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-maroon-950 sm:px-6">
                                {{ $loa->full_name }}
                                @if ($loa->program || $loa->department)
                                    <div class="text-xs text-maroon-900/55">
                                        {{ optional($loa->program)->code ?? '—' }} · {{ optional($loa->department)->name ?? '—' }}
                                    </div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-maroon-900/85 sm:px-6">
                                {{ optional($loa->start_date)->toFormattedDateString() ?? '—' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-maroon-900/85 sm:px-6">
                                {{ optional($loa->return_date)->toFormattedDateString() ?? '—' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm sm:px-6">
                                <div class="flex flex-col gap-1">
                                    <span class="{{ $loa->statusBadgeClasses() }}">{{ $loa->statusLabel() }}</span>
                                    @if ($loa->status === 'submitted')
                                        <span class="{{ $stageClasses }}">{{ $stageLabel }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm sm:px-6">
                                <a href="{{ route('staff.loa.show', $loa) }}"
                                   class="inline-flex items-center rounded-md bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                    View Form
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
