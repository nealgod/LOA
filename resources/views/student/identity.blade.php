@extends('layouts.app')

@section('title', 'Request LOA')

@section('content')
<x-page-shell eyebrow="Student · no account needed" title="Request Leave of Absence">
    <x-slot:lead>
        Enter your official EVSU email address. We will send a 6-digit verification code to confirm it's you.
    </x-slot:lead>

    @if ($errors->has('token'))
        <p class="mb-4 rounded-lg border border-maroon-600/20 bg-maroon-600/10 px-4 py-3 text-sm text-maroon-800">{{ $errors->first('token') }}</p>
    @endif

    <form method="POST" action="{{ route('student.identity.store') }}" class="space-y-5 rounded-2xl border border-maroon-900/10 bg-white p-6 sm:p-8">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-maroon-900">EVSU email address</label>
            <input id="email" name="email" type="email"
                   value="{{ old('email') }}"
                   required autocomplete="email"
                   autofocus
                   placeholder="name@evsu.edu.ph"
                   class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2 @error('email') border-red-400 bg-red-50 @enderror">
            <p class="mt-1.5 text-xs text-maroon-800/60">Only <strong>@evsu.edu.ph</strong> addresses are accepted. Gmail, Yahoo, and other personal emails will be rejected.</p>
            @error('email')
                <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="w-full rounded-xl bg-maroon-800 px-4 py-3.5 text-base font-semibold text-cream-50 hover:bg-maroon-700">
            Send verification code
        </button>
    </form>
</x-page-shell>
@endsection
