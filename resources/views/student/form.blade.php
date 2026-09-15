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

        <div class="grid gap-4 sm:grid-cols-2">
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
        </div>
        <script type="application/json" data-programs-json>@json($programs)</script>

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
                <input id="parent_phone" name="parent_phone" required value="{{ old('parent_phone', $loa->parent_phone) }}"
                    class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2"
                    placeholder="09xx xxx xxxx">
                @error('parent_phone') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="attachments" class="block text-sm font-medium text-maroon-900">Supporting files</label>
            <input id="attachments" name="attachments[]" type="file" required multiple accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp"
                class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base file:mr-3 file:rounded-lg file:border-0 file:bg-maroon-800 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-cream-50">
            <p class="mt-2 text-xs text-maroon-800/70">You may attach more than one PDF or image (JPG, PNG, WEBP). Each file up to 5 MB, 10 files max.</p>
            @error('attachments') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            @error('attachments.*') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="w-full rounded-xl bg-maroon-800 px-4 py-3.5 text-base font-semibold text-cream-50 hover:bg-maroon-700">
            Submit LOA request
        </button>
    </form>
</x-page-shell>
@endsection
