@extends('layouts.app')

@section('title', $loa->control_number)

@section('content')
<x-page-shell eyebrow="{{ $loa->control_number }}" title="{{ $loa->full_name }}" width="3xl">
    <x-slot:lead>
        {{ $loa->department?->name }} · {{ $loa->program?->name }}
    </x-slot:lead>

    <div class="space-y-4 rounded-2xl border border-maroon-900/10 bg-white p-6 sm:p-8 text-base text-maroon-800/80">
        <p><span class="font-semibold text-maroon-900">Student ID:</span> {{ $loa->student_id }}</p>
        <p><span class="font-semibold text-maroon-900">Email:</span> {{ $loa->email }}</p>
        <p><span class="font-semibold text-maroon-900">Leave:</span> {{ $loa->start_date?->format('M j, Y') }} to {{ $loa->return_date?->format('M j, Y') }}</p>
        <p><span class="font-semibold text-maroon-900">Reason:</span> {{ $loa->reason }}</p>
        <p><span class="font-semibold text-maroon-900">Parent/guardian:</span> {{ $loa->parent_full_name }} ({{ $loa->parent_relationship }}) · {{ $loa->parent_phone }}</p>

        <div>
            <p class="font-semibold text-maroon-900">Attachments</p>
            <ul class="mt-2 space-y-2">
                @forelse ($loa->attachments as $file)
                    <li>
                        <a href="{{ route('staff.loa.attachment', [$loa, $file]) }}" class="font-semibold text-maroon-700 underline">
                            {{ $file->original_name }}
                        </a>
                    </li>
                @empty
                    <li>No files attached.</li>
                @endforelse
            </ul>
        </div>

        <a href="{{ route('staff.dashboard') }}" class="inline-flex pt-2 text-sm font-semibold text-maroon-700">Back to queue</a>
    </div>
</x-page-shell>
@endsection
