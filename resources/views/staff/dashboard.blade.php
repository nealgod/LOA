@extends('layouts.app')

@section('title', 'Staff dashboard')

@section('content')
<x-page-shell title="Welcome, {{ auth()->user()->name }}" width="3xl">
    <x-slot:lead>
        Signed in as <strong>{{ auth()->user()->role->label() }}</strong>
        @if (auth()->user()->department)
            · {{ auth()->user()->department->name }}
        @endif
        @if (auth()->user()->role === \App\Enums\UserRole::DepartmentHead)
            You only see applications from your department, even when programs differ.
        @endif
    </x-slot:lead>

    <div class="rounded-2xl border border-maroon-900/10 bg-white p-6 sm:p-8">
        @if ($requests->isEmpty())
            <p class="text-base leading-relaxed text-maroon-800/80">No submitted LOA applications in your queue yet.</p>
        @else
            <ul class="divide-y divide-maroon-900/10">
                @foreach ($requests as $loa)
                    <li class="py-4 first:pt-0 last:pb-0">
                        <a href="{{ route('staff.loa.show', $loa) }}" class="block hover:opacity-80">
                            <p class="font-semibold text-maroon-900">{{ $loa->control_number }}</p>
                            <p class="mt-1 text-sm text-maroon-800/80">{{ $loa->full_name }} · {{ $loa->program?->name }}</p>
                            <p class="mt-1 text-xs text-maroon-800/60">{{ $loa->department?->name }} · submitted {{ $loa->submitted_at?->format('M j, Y') }}</p>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-page-shell>
@endsection
