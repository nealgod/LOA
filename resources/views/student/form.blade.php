@extends('layouts.app')

@section('title', 'LOA form')

@section('content')
<x-page-shell eyebrow="EVSU-SASO-F-040" title="Leave of Absence application" width="2xl">
    <x-slot:lead>
        Verified as <strong>{{ $access->full_name }}</strong> ({{ $access->student_id }}) · {{ $access->email }}.
        Leave may not exceed one year.
    </x-slot:lead>

    <form method="POST" action="{{ route('student.form.store', $token) }}" enctype="multipart/form-data" class="space-y-4 rounded-2xl border border-maroon-900/10 bg-white p-6 sm:p-8">
        @csrf

        {{-- Student identity: name first, then student number. Name can be corrected. --}}
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="full_name" class="block text-sm font-medium text-maroon-900">Full name</label>
                <input id="full_name" name="full_name" required value="{{ old('full_name', $access->full_name) }}"
                    placeholder="Lastname, Firstname, Middlename"
                    class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                @error('full_name') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-maroon-900">Student number</label>
                <input type="text" value="{{ $access->student_id }}" disabled
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

        <div>
            <label for="reason" class="block text-sm font-medium text-maroon-900">Reason for leave</label>
            <textarea id="reason" name="reason" rows="5" required
                class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">{{ old('reason', $loa->reason) }}</textarea>
            @error('reason') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
        </div>

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
                        value="{{ old('parent_phone', ltrim((string) $loa->parent_phone, '+63')) }}"
                        class="flex-1 bg-cream-50 px-3 py-3 text-base outline-none"
                        placeholder="917-123-4567"
                        maxlength="12"
                        inputmode="numeric"
                        data-phone-input>
                </div>
                @error('parent_phone') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="attachments" class="block text-sm font-medium text-maroon-900">Supporting files <span class="font-normal text-maroon-800/50">(optional)</span></label>
            <input id="attachments" name="attachments[]" type="file" multiple accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp"
                class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base file:mr-3 file:rounded-lg file:border-0 file:bg-maroon-800 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-cream-50">
            <p class="mt-2 text-xs text-maroon-800/70">PDF or image (JPG, PNG, WEBP) — up to 10 files, 5 MB each. Not required but recommended.</p>
            @error('attachments') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            @error('attachments.*') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="w-full rounded-xl bg-maroon-800 px-4 py-3.5 text-base font-semibold text-cream-50 hover:bg-maroon-700">
            Submit LOA request
        </button>
    </form>
</x-page-shell>
@endsection
