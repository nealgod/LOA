@extends('layouts.staff')

@section('eyebrow', $loa->control_number)
@section('title', $loa->full_name)

@section('content')

    {{-- ── Top meta bar ─────────────────────────────────────────────────────── --}}
    <div class="mb-6 flex flex-wrap items-center gap-3 rounded-2xl border border-maroon-900/10 bg-white/70 px-5 py-3 shadow-sm">
        <span class="text-sm text-maroon-900/70">
            Viewing as <strong class="text-maroon-950">{{ auth()->user()->name }}</strong>
            · {{ auth()->user()->role->label() }}
            @if (auth()->user()->department_id)
                · {{ auth()->user()->department->name ?? '' }}
            @endif
        </span>
        <span class="text-maroon-900/20">|</span>
        <span class="text-sm text-maroon-900/70">{{ $loa->department?->name }} · {{ $loa->program?->name }}</span>
        <x-loa-status-badge :loa="$loa" />
        @php
            $stageStatus = $loa->currentStageStatus();
            $stageName   = $loa->determineCurrentStage();
        @endphp
        @if ($stageStatus)
            <span class="{{ $stageStatus->badgeClasses() }}">{{ $loa->currentStageLabel() }}</span>
        @elseif ($stageName === 'done')
            <span class="rounded-full border border-emerald-600 bg-emerald-600 px-3 py-1 text-xs font-medium text-white">Fully Approved</span>
        @elseif ($stageName === 'rejected')
            <span class="rounded-full border border-red-600 bg-red-600 px-3 py-1 text-xs font-medium text-white">Rejected</span>
        @endif
        @if ($loa->submitted_at)
            <span class="ml-auto text-xs text-maroon-800/50">Submitted {{ $loa->submitted_at->format('M j, Y g:i A') }}</span>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- ── Left column: student details + attachments ───────────────────── --}}
        <div class="space-y-6 lg:col-span-2">

            {{-- Student details --}}
            <div class="rounded-2xl border border-maroon-900/10 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-maroon-600">Student Details</h2>
                <div class="grid grid-cols-1 gap-x-8 gap-y-3 text-sm sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-maroon-900/50">Student ID</p>
                        <p class="mt-0.5 font-mono font-semibold text-maroon-950">{{ $loa->student_id ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-maroon-900/50">Email</p>
                        <p class="mt-0.5 text-maroon-950">{{ $loa->email }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-maroon-900/50">Year Level</p>
                        <p class="mt-0.5 text-maroon-950">{{ $loa->year_level ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-maroon-900/50">Program</p>
                        <p class="mt-0.5 text-maroon-950">{{ $loa->program?->name ?? '—' }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-xs font-medium uppercase tracking-wider text-maroon-900/50">Leave Period</p>
                        <p class="mt-0.5 font-semibold text-maroon-950">
                            {{ $loa->start_date?->format('F j, Y') ?? '—' }}
                            <span class="mx-1 text-maroon-900/40">→</span>
                            {{ $loa->return_date?->format('F j, Y') ?? '—' }}
                            @if ($loa->start_date && $loa->return_date)
                                <span class="ml-2 text-xs font-normal text-maroon-900/50">({{ $loa->start_date->diffInDays($loa->return_date) }} days)</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="mt-4 border-t border-maroon-900/5 pt-4">
                    <p class="text-xs font-medium uppercase tracking-wider text-maroon-900/50 mb-1">Reason for Leave</p>
                    <p class="text-sm leading-relaxed text-maroon-800/85 whitespace-pre-wrap">{{ $loa->reason }}</p>
                </div>

                <div class="mt-4 border-t border-maroon-900/5 pt-4">
                    <p class="text-xs font-medium uppercase tracking-wider text-maroon-900/50 mb-1">Parent / Guardian</p>
                    <p class="text-sm text-maroon-800/85">
                        <span class="font-semibold text-maroon-950">{{ $loa->parent_full_name }}</span>
                        <span class="text-maroon-900/50"> ({{ $loa->parent_relationship }})</span>
                        <span class="mx-1 text-maroon-900/30">·</span>
                        {{ $loa->parent_phone }}
                    </p>
                </div>
            </div>

            {{-- Attachments --}}
            <div class="rounded-2xl border border-maroon-900/10 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-maroon-600">
                    Supporting Documents
                    <span class="ml-1 rounded-full bg-maroon-100 px-2 py-0.5 text-xs font-bold text-maroon-700">{{ $loa->attachments->count() }}</span>
                </h2>

                @forelse ($loa->attachments as $file)
                    <div class="mb-4 overflow-hidden rounded-xl border border-maroon-900/10 last:mb-0">
                        {{-- File header --}}
                        <div class="flex items-center justify-between gap-3 bg-maroon-950/[0.03] px-4 py-2.5">
                            <div class="flex min-w-0 items-center gap-2">
                                <span class="text-base leading-none">
                                    @if ($file->isImage()) 🖼 @else 📄 @endif
                                </span>
                                <span class="truncate text-sm font-medium text-maroon-950">{{ $file->original_name }}</span>
                                <span class="shrink-0 text-xs text-maroon-900/40">{{ number_format($file->size / 1024, 1) }} KB</span>
                            </div>
                            <a href="{{ route('staff.loa.attachment', [$loa, $file]) }}"
                               class="shrink-0 rounded-md border border-maroon-900/15 bg-white px-3 py-1 text-xs font-semibold text-maroon-700 hover:bg-maroon-50"
                               download="{{ $file->original_name }}">
                                Download
                            </a>
                        </div>

                        {{-- Inline image preview --}}
                        @if ($file->isImage())
                            <div class="bg-maroon-950/[0.02] p-3">
                                <img src="{{ route('staff.loa.attachment', [$loa, $file]) }}"
                                     alt="{{ $file->original_name }}"
                                     loading="lazy"
                                     class="max-h-96 w-auto rounded-lg shadow-sm cursor-zoom-in object-contain"
                                     data-lightbox-src="{{ route('staff.loa.attachment', [$loa, $file]) }}"
                                     onclick="openLightbox(this.dataset.lightboxSrc, '{{ addslashes($file->original_name) }}')">
                            </div>
                        @else
                            {{-- PDF / other: show download prompt --}}
                            <div class="flex items-center gap-3 bg-cream-50 px-4 py-3">
                                <span class="text-2xl">📄</span>
                                <div class="text-sm">
                                    <p class="font-medium text-maroon-950">{{ $file->original_name }}</p>
                                    <a href="{{ route('staff.loa.attachment', [$loa, $file]) }}"
                                       target="_blank"
                                       class="text-xs text-maroon-700 underline underline-offset-2 hover:text-maroon-600">
                                        Open in new tab ↗
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-sm italic text-maroon-900/50">No files attached to this application.</p>
                @endforelse
            </div>

            {{-- Rejection details (if rejected) --}}
            @if ($loa->status === 'rejected' && ($loa->rejectedByActor || $loa->rejection_reason))
                <div class="rounded-2xl border border-red-200 bg-red-50 p-6 shadow-sm">
                    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wider text-red-600">Rejection Details</h2>
                    @if ($loa->rejectedByActor)
                        <p class="text-sm text-red-700">
                            Rejected by <strong>{{ $loa->rejectedByActor->name }}</strong>
                            ({{ $loa->rejectedByActor->role->label() }})
                            @if ($loa->rejected_at)
                                on {{ $loa->rejected_at->format('F j, Y \a\t g:i A') }}
                            @endif
                        </p>
                    @endif
                    @if ($loa->rejection_reason)
                        <div class="mt-3 rounded-lg border border-red-200 bg-white px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-wider text-red-500 mb-1">Stated reason</p>
                            <p class="text-sm text-red-800 whitespace-pre-wrap">{{ $loa->rejection_reason }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- ── Right column: approval timeline + actions ────────────────────── --}}
        <div class="space-y-6">

            {{-- Approval timeline --}}
            <div class="rounded-2xl border border-maroon-900/10 bg-white p-6 shadow-sm">
                <h2 class="mb-5 text-sm font-semibold uppercase tracking-wider text-maroon-600">Approval Timeline</h2>

                @php
                    $stages = [
                        [
                            'label'  => 'Department Head',
                            'status' => $loa->dept_head_status,
                            'actor'  => $loa->deptHeadActor,
                            'at'     => $loa->dept_head_at,
                        ],
                        [
                            'label'  => 'SASO Officer',
                            'status' => $loa->saso_status,
                            'actor'  => $loa->sasoActor,
                            'at'     => $loa->saso_at,
                        ],
                        [
                            'label'  => 'Campus Director',
                            'status' => $loa->campus_director_status,
                            'actor'  => $loa->campusDirectorActor,
                            'at'     => $loa->campus_director_at,
                        ],
                        [
                            'label'  => 'Registrar',
                            'status' => $loa->registrar_status,
                            'actor'  => $loa->registrarActor,
                            'at'     => $loa->registrar_at,
                        ],
                        [
                            'label'  => 'Guidance Office',
                            'status' => $loa->guidance_status,
                            'actor'  => $loa->guidanceActor,
                            'at'     => $loa->guidance_at,
                        ],
                    ];
                @endphp

                <ol class="relative space-y-0 border-l-2 border-maroon-900/10 pl-6">
                    @foreach ($stages as $i => $stage)
                        @php
                            $s = $stage['status'];
                            $isDone      = $s?->value === 'approved';
                            $isRejected  = $s?->value === 'rejected';
                            $isPending   = $s?->value === 'pending';

                            $dotColor = match(true) {
                                $isDone     => 'bg-emerald-500 ring-emerald-200',
                                $isRejected => 'bg-red-500 ring-red-200',
                                $isPending  => 'bg-amber-400 ring-amber-100',
                                default     => 'bg-maroon-900/15 ring-maroon-900/5',
                            };
                            $dotIcon = match(true) {
                                $isDone     => '✓',
                                $isRejected => '✕',
                                $isPending  => '…',
                                default     => (string)($i + 1),
                            };
                        @endphp
                        <li class="relative pb-7 last:pb-0">
                            {{-- Connector dot --}}
                            <span class="absolute -left-[1.6rem] flex h-7 w-7 items-center justify-center rounded-full ring-4 text-xs font-bold
                                         {{ $dotColor }} {{ $isDone ? 'text-white' : ($isRejected ? 'text-white' : ($isPending ? 'text-amber-900' : 'text-maroon-900/40')) }}">
                                {{ $dotIcon }}
                            </span>

                            <div class="ml-1">
                                <p class="text-sm font-semibold text-maroon-950">{{ $stage['label'] }}</p>

                                @if ($isDone || $isRejected)
                                    @if ($stage['actor'])
                                        <p class="text-xs text-maroon-900/70 mt-0.5">
                                            {{ $isDone ? 'Approved' : 'Rejected' }} by
                                            <span class="font-medium text-maroon-950">{{ $stage['actor']->name }}</span>
                                        </p>
                                    @endif
                                    @if ($stage['at'])
                                        <p class="text-xs text-maroon-900/50 mt-0.5">{{ $stage['at']->format('M j, Y · g:i A') }}</p>
                                    @endif
                                @elseif ($isPending)
                                    <p class="mt-0.5 text-xs font-medium text-amber-700">Awaiting action</p>
                                @else
                                    <p class="mt-0.5 text-xs text-maroon-900/40">Locked — prior stage pending</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>

                {{-- Final status --}}
                @if ($stageName === 'done')
                    <div class="mt-5 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-center">
                        <p class="text-sm font-semibold text-emerald-700">✓ Fully Approved</p>
                        <p class="text-xs text-emerald-600 mt-0.5">All approval stages completed.</p>
                    </div>
                @elseif ($loa->status === 'rejected')
                    <div class="mt-5 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-center">
                        <p class="text-sm font-semibold text-red-700">✕ Rejected</p>
                        <p class="text-xs text-red-600 mt-0.5">Pipeline terminated.</p>
                    </div>
                @endif
            </div>

            {{-- Action buttons --}}
            @canany(['approve', 'reject'], $loa)
                <div class="rounded-2xl border border-maroon-900/10 bg-white p-5 shadow-sm space-y-3">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-maroon-600">Your Action</h2>
                    <p class="text-xs text-maroon-900/60">
                        Acting as <strong>{{ auth()->user()->role->label() }}</strong>.
                        Stage: <strong>{{ $loa->currentStageLabel() }}</strong>.
                    </p>

                    @can('approve', $loa)
                        <form method="POST" action="{{ route('staff.loa.approve', $loa) }}">
                            @csrf
                            <button type="submit"
                                    class="w-full rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                                ✓ Approve LOA
                            </button>
                        </form>
                    @endcan

                    @can('reject', $loa)
                        <button type="button"
                                data-reject-trigger
                                data-control="{{ $loa->control_number }}"
                                data-action="{{ route('staff.loa.reject', $loa) }}"
                                class="w-full rounded-xl border border-red-600 bg-white px-4 py-2.5 text-sm font-semibold text-red-700 shadow-sm hover:bg-red-50">
                            ✕ Reject LOA
                        </button>
                    @endcan
                </div>
            @endcanany

            {{-- Navigation --}}
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('staff.pipeline') }}"
                   class="flex-1 rounded-xl border border-maroon-900/15 bg-white px-4 py-2.5 text-center text-sm font-semibold text-maroon-700 shadow-sm hover:bg-maroon-50">
                    ← Pipeline
                </a>
                <a href="{{ route('staff.dashboard') }}"
                   class="flex-1 rounded-xl border border-maroon-900/15 bg-white px-4 py-2.5 text-center text-sm font-semibold text-maroon-700 shadow-sm hover:bg-maroon-50">
                    ← Dashboard
                </a>
            </div>
        </div>
    </div>

    {{-- Reject modal (same as pipeline, reused here) --}}
    <div id="reject-modal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-maroon-950/60 backdrop-blur-sm p-4"
         role="dialog" aria-modal="true" aria-labelledby="reject-modal-title">
        <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl">
            <div class="border-b border-maroon-900/10 px-6 py-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-red-600">Reject Application</p>
                <h3 id="reject-modal-title" class="mt-1 text-lg font-bold text-maroon-950">
                    Reject <span id="reject-control-display" class="font-mono"></span>
                </h3>
            </div>
            <form id="reject-form" method="POST">
                @csrf
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label for="reject-reason" class="block text-sm font-medium text-maroon-900 mb-1">
                            Reason for rejection <span class="text-red-600">*</span>
                        </label>
                        <textarea id="reject-reason" name="reason" rows="4" required
                                  placeholder="Explain why this LOA request is being rejected…"
                                  class="w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-sm outline-none ring-red-500 focus:ring-2 resize-none"></textarea>
                        <p class="mt-1 text-xs text-maroon-900/50">Recorded in the audit trail and shown to relevant staff.</p>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-maroon-900/10 px-6 py-4">
                    <button type="button" id="reject-cancel"
                            class="rounded-lg border border-maroon-900/20 bg-white px-4 py-2 text-sm font-semibold text-maroon-700 hover:bg-maroon-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                        Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Lightbox for image attachments --}}
    <div id="lightbox"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/85 p-4"
         onclick="closeLightbox()">
        <div class="relative max-h-full max-w-5xl" onclick="event.stopPropagation()">
            <button onclick="closeLightbox()"
                    class="absolute -top-10 right-0 text-white/80 hover:text-white text-sm font-semibold">
                ✕ Close
            </button>
            <img id="lightbox-img" src="" alt="" class="max-h-[85vh] max-w-full rounded-xl shadow-2xl object-contain">
            <p id="lightbox-caption" class="mt-2 text-center text-xs text-white/60"></p>
        </div>
    </div>
@endsection

@push('scripts')
<script>
// ── Lightbox ──────────────────────────────────────────────────────────────────
function openLightbox(src, caption) {
    document.getElementById('lightbox-img').src = src;
    document.getElementById('lightbox-caption').textContent = caption;
    const lb = document.getElementById('lightbox');
    lb.classList.remove('hidden');
    lb.classList.add('flex');
}
function closeLightbox() {
    const lb = document.getElementById('lightbox');
    lb.classList.add('hidden');
    lb.classList.remove('flex');
    document.getElementById('lightbox-img').src = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

// ── Reject modal ─────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const modal        = document.getElementById('reject-modal');
    const form         = document.getElementById('reject-form');
    const controlLabel = document.getElementById('reject-control-display');
    const reasonField  = document.getElementById('reject-reason');
    const cancelBtn    = document.getElementById('reject-cancel');

    const open = (control, action) => {
        controlLabel.textContent = control;
        form.action = action;
        reasonField.value = '';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        reasonField.focus();
    };
    const close = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        reasonField.value = '';
    };

    document.querySelectorAll('[data-reject-trigger]').forEach(btn => {
        btn.addEventListener('click', () => open(btn.dataset.control, btn.dataset.action));
    });
    cancelBtn?.addEventListener('click', close);
    modal?.addEventListener('click', e => { if (e.target === modal) close(); });
});
</script>
@endpush
