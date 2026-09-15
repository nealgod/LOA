@extends('layouts.app')

@section('title', 'Request LOA')

@section('content')
<x-page-shell eyebrow="Student · no account needed" title="Verify your identity">
    <x-slot:lead>
        Enter your Student ID, full name, and official EVSU email. If they match an active student record,
        we email you a private link to EVSU-SASO-F-040.
    </x-slot:lead>

    @if ($errors->has('token'))
        <p class="mb-4 rounded-lg border border-maroon-600/20 bg-maroon-600/10 px-4 py-3 text-sm text-maroon-800">{{ $errors->first('token') }}</p>
    @endif

    <form method="POST" action="{{ route('student.identity.store') }}" class="space-y-4 rounded-2xl border border-maroon-900/10 bg-white p-6 sm:p-8">
        @csrf

        <div>
            <label for="student_id" class="block text-sm font-medium text-maroon-900">Student ID</label>
            <input id="student_id" name="student_id" value="{{ old('student_id') }}" required autocomplete="off"
                class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2"
                placeholder="e.g. 2021-12345">
            @error('student_id') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="full_name" class="block text-sm font-medium text-maroon-900">Full name</label>
            <input id="full_name" name="full_name" value="{{ old('full_name') }}" required autocomplete="name"
                class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2"
                placeholder="Lastname, Firstname, Middlename">
            @error('full_name') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-maroon-900">EVSU email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2"
                placeholder="name@evsu.edu.ph">
            @error('email') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="w-full rounded-xl bg-maroon-800 px-4 py-3.5 text-base font-semibold text-cream-50 hover:bg-maroon-700">
            Send form link to email
        </button>
    </form>
</x-page-shell>
@endsection
