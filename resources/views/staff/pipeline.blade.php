@extends('layouts.staff')

@section('eyebrow', 'Approval Pipeline & Records')
@section('title', 'Approval Pipeline & Records')

@section('content')
    <div class="mb-8 rounded-2xl border border-maroon-900/10 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-maroon-600">Sequentially Enforced Order</p>
        <h2 class="mt-1 text-lg font-semibold text-maroon-950">Sequential Approval Pipeline &amp; Authority Clearances</h2>
        <p class="mt-2 text-sm text-maroon-900/70">
            Stage actions are locked in order: <span class="font-semibold">Department Head → SASO Officer → Campus Director → Registrar → Guidance Office</span>.
            Each stage must be approved before the next office can act. A rejection at any stage terminates the pipeline.
        </p>
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
                        <th scope="col" class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-4">App ID</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-4">Student ID</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-4">Student Name</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-4">Dept Head Status</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-4">SASO Status</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-4">Campus Director Status</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-4">Registrar Status</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-4">Guidance Status</th>
                        <th scope="col" class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-4">Action Options</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-maroon-900/10 bg-white">
                    @foreach ($loas as $loa)
                        @php
                            $canApprove = \Illuminate\Support\Facades\Gate::allows('approve', $loa);
                            $canReject  = \Illuminate\Support\Facades\Gate::allows('reject', $loa);
                            $isAdmin    = $user->role->is(\App\Enums\UserRole::Administrator);

                            $statusBadge = function ($status) {
                                if (! $status) {
                                    return '<span class="inline-flex items-center rounded-full border border-gray-300 bg-gray-50 px-3 py-1 text-xs font-medium text-gray-500">—</span>';
                                }
                                return '<span class="'.e($status->badgeClasses()).'">'.e($status->label()).'</span>';
                            };
                        @endphp
                        <tr class="hover:bg-maroon-950/[0.02]">
                            <td class="whitespace-nowrap px-3 py-3 font-mono text-sm font-bold text-maroon-900 sm:px-4">
                                {{ $loa->control_number }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-3 text-sm text-maroon-900/85 sm:px-4">
                                {{ $loa->student_id ?? '—' }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-3 text-sm font-medium text-maroon-950 sm:px-4">
                                {{ $loa->full_name }}
                                @if ($loa->program || $loa->department)
                                    <div class="text-xs text-maroon-900/55">
                                        {{ optional($loa->program)->code ?? '—' }} · {{ optional($loa->department)->name ?? '—' }}
                                    </div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-3 text-sm sm:px-4">{!! $statusBadge($loa->dept_head_status) !!}</td>
                            <td class="whitespace-nowrap px-3 py-3 text-sm sm:px-4">{!! $statusBadge($loa->saso_status) !!}</td>
                            <td class="whitespace-nowrap px-3 py-3 text-sm sm:px-4">{!! $statusBadge($loa->campus_director_status) !!}</td>
                            <td class="whitespace-nowrap px-3 py-3 text-sm sm:px-4">{!! $statusBadge($loa->registrar_status) !!}</td>
                            <td class="whitespace-nowrap px-3 py-3 text-sm sm:px-4">{!! $statusBadge($loa->guidance_status) !!}</td>
                            <td class="whitespace-nowrap px-3 py-3 text-right text-sm sm:px-4">
                                <div class="inline-flex flex-wrap items-center justify-end gap-2">
                                    <a href="{{ route('staff.loa.show', $loa) }}"
                                       class="inline-flex items-center rounded-md border border-maroon-900/20 bg-white px-3 py-1.5 text-xs font-semibold text-maroon-900 shadow-sm hover:bg-maroon-950/5">
                                        View
                                    </a>

                                    @if ($canApprove)
                                        <form method="POST" action="{{ route('staff.loa.approve', $loa) }}" class="m-0 inline-flex">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-500">
                                                Approve LOA
                                            </button>
                                        </form>
                                    @endif

                                    @if ($canReject)
                                        <button type="button"
                                                data-reject-trigger
                                                data-control="{{ $loa->control_number }}"
                                                data-action="{{ route('staff.loa.reject', $loa) }}"
                                                class="inline-flex items-center rounded-md border border-red-600 bg-white px-3 py-1.5 text-xs font-semibold text-red-700 shadow-sm hover:bg-red-50">
                                            Reject LOA
                                        </button>
                                    @endif

                                    @if ($isAdmin)
                                        <button type="button" disabled
                                                class="inline-flex cursor-not-allowed items-center rounded-md border border-gray-300 bg-gray-50 px-3 py-1.5 text-xs font-semibold text-gray-400 shadow-sm opacity-60">
                                            Delete
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Reject modal --}}
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
                        <p class="mt-1 text-xs text-maroon-900/50">This reason will be recorded in the audit trail and shown to relevant staff.</p>
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
@endsection

@push('scripts')
<script>
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

    cancelBtn.addEventListener('click', close);

    modal.addEventListener('click', e => {
        if (e.target === modal) close();
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') close();
    });
});
</script>
@endpush
