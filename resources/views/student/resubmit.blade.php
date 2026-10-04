@extends('layouts.app')

@section('title', 'Resubmit LOA — ' . $loa->control_number)

@section('content')
<x-page-shell eyebrow="EVSU-SASO-F-040 · Resubmission" title="Resubmit Leave of Absence" width="2xl">
    <x-slot:lead>
        Updating your application <strong>{{ $loa->control_number }}</strong> for
        <strong>{{ $loa->full_name }}</strong> · {{ $loa->email }}.
        Address the rejection reason below and resubmit.
    </x-slot:lead>

    {{-- Rejection reason banner --}}
    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-5">
        <p class="text-xs font-semibold uppercase tracking-wider text-red-600 mb-2">Rejection Reason</p>
        @if ($loa->rejectedByActor)
            <p class="text-sm text-red-700 mb-1">
                Rejected by <strong>{{ $loa->rejectedByActor->name }}</strong>
                ({{ $loa->rejectedByActor->role->label() }})
                @if ($loa->rejected_at)
                    on {{ $loa->rejected_at->format('F j, Y \a\t g:i A') }}
                @endif
            </p>
        @endif
        @if ($loa->rejection_reason)
            <p class="text-sm text-red-800 whitespace-pre-wrap mt-2 rounded-lg border border-red-200 bg-white px-4 py-3">{{ $loa->rejection_reason }}</p>
        @endif
    </div>

    <form method="POST" action="{{ route('student.resubmit.store', $token) }}" enctype="multipart/form-data"
          class="space-y-4 rounded-2xl border border-maroon-900/10 bg-white p-6 sm:p-8">
        @csrf

        {{-- Student identity — read-only --}}
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-maroon-900">Full name</label>
                <input type="text" value="{{ $loa->full_name }}" disabled
                       class="mt-1 w-full rounded-xl border border-maroon-900/10 bg-maroon-900/5 px-3 py-3 text-base text-maroon-800/70 cursor-not-allowed">
            </div>
            <div>
                <label class="block text-sm font-medium text-maroon-900">Student number</label>
                <input type="text" value="{{ $loa->student_id }}" disabled
                       class="mt-1 w-full rounded-xl border border-maroon-900/10 bg-maroon-900/5 px-3 py-3 text-base text-maroon-800/70 cursor-not-allowed">
            </div>
        </div>

        {{-- Department, Program, Year level --}}
        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label for="department_id" class="block text-sm font-medium text-maroon-900">Department</label>
                <select id="department_id" name="department_id" required data-department-filter
                        class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                    <option value="">Select department</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected(old('department_id', $loa->department_id) == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
                @error('department_id') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="program_id" class="block text-sm font-medium text-maroon-900">Program</label>
                <select id="program_id" name="program_id" required data-program-select data-selected-program="{{ old('program_id', $loa->program_id) }}"
                        class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                    <option value="">Select department first</option>
                </select>
                @error('program_id') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="year_level" class="block text-sm font-medium text-maroon-900">Year level</label>
                <select id="year_level" name="year_level" required data-year-select
                        class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                    <option value="">Select program first</option>
                </select>
                @error('year_level') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>
        </div>
        <script type="application/json" data-programs-json>@json($programs)</script>
        <input type="hidden" id="year_level_saved" value="{{ old('year_level', $loa->year_level ?? '') }}">

        {{-- Leave dates --}}
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="start_date" class="block text-sm font-medium text-maroon-900">Expected start date</label>
                <input id="start_date" name="start_date" type="date" required value="{{ old('start_date', $loa->start_date?->format('Y-m-d')) }}"
                       class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                @error('start_date') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="return_date" class="block text-sm font-medium text-maroon-900">Expected return date</label>
                <input id="return_date" name="return_date" type="date" required value="{{ old('return_date', $loa->return_date?->format('Y-m-d')) }}"
                       class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                @error('return_date') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Reason --}}
        <div>
            <label for="reason" class="block text-sm font-medium text-maroon-900">Updated reason for leave</label>
            <textarea id="reason" name="reason" rows="5" required
                      class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">{{ old('reason', $loa->reason) }}</textarea>
            @error('reason') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
        </div>

        {{-- Parent / Guardian --}}
        <div>
            <label for="parent_full_name" class="block text-sm font-medium text-maroon-900">Parent / guardian full name</label>
            <input id="parent_full_name" name="parent_full_name" required value="{{ old('parent_full_name', $loa->parent_full_name) }}"
                   placeholder="Lastname, Firstname, Middlename"
                   class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
            @error('parent_full_name') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="parent_relationship" class="block text-sm font-medium text-maroon-900">Relationship</label>
                <input id="parent_relationship" name="parent_relationship" required value="{{ old('parent_relationship', $loa->parent_relationship) }}"
                       class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2"
                       placeholder="Mother, Father, Guardian…">
                @error('parent_relationship') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="parent_phone" class="block text-sm font-medium text-maroon-900">Active phone number</label>
                <div class="mt-1 flex rounded-xl border border-maroon-900/15 bg-cream-50 focus-within:ring-2 focus-within:ring-maroon-700 overflow-hidden">
                    <span class="flex items-center bg-maroon-900/5 border-r border-maroon-900/15 px-3 text-sm font-medium text-maroon-700 select-none">+63</span>
                    <input id="parent_phone" name="parent_phone" type="tel" required
                           value="{{ old('parent_phone', (function() use ($loa) {
                               $d = substr((string) $loa->parent_phone, 3); // strip +63 → 10 digits
                               return strlen($d) === 10
                                   ? substr($d,0,3).'-'.substr($d,3,3).'-'.substr($d,6)
                                   : $d;
                           })()) }}"
                           class="flex-1 bg-cream-50 px-3 py-3 text-base outline-none"
                           placeholder="917-123-4567"
                           maxlength="12"
                           inputmode="numeric"
                           data-phone-input>
                </div>
                @error('parent_phone') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Additional attachments --}}
        <div>
            <label for="attachments" class="block text-sm font-medium text-maroon-900">
                Add or replace supporting files
                <span class="font-normal text-maroon-800/50">(optional)</span>
            </label>
            @if ($loa->attachments->count() > 0)
                <p class="mt-1 text-xs text-maroon-800/60">
                    Previously attached: {{ $loa->attachments->count() }} file(s).
                    Any new files you add here will be added alongside the existing ones.
                </p>
            @endif
            <input id="attachments" name="attachments[]" type="file" multiple
                   accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp"
                   data-attachments-input
                   class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base file:mr-3 file:rounded-lg file:border-0 file:bg-maroon-800 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-cream-50 cursor-pointer">
            <p class="mt-1 text-xs text-maroon-800/70">PDF or image (JPG, PNG, WEBP) — up to 10 files, 5 MB each.</p>

            <div id="attachments-container" class="mt-3 hidden space-y-2" data-attachments-container>
                <div class="flex items-center justify-between text-xs font-semibold text-maroon-900">
                    <span data-attachments-count>Selected files (0 of 10)</span>
                </div>
                <div class="space-y-1.5" data-attachments-list></div>
            </div>
            <p class="mt-2 hidden text-xs font-medium text-maroon-600" data-attachments-error></p>
            @error('attachments') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            @error('attachments.*') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
        </div>

        <button type="submit"
                class="w-full rounded-xl bg-maroon-800 px-4 py-3.5 text-base font-semibold text-cream-50 hover:bg-maroon-700">
            Resubmit LOA Application
        </button>
    </form>
</x-page-shell>
@endsection
