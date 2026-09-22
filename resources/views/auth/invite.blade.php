@extends('layouts.app')

@section('title', 'Set up your account')

@section('content')
<x-page-shell eyebrow="Staff invitation" title="Set up your account">
    <x-slot:lead>
        You have been invited as <strong>{{ $user->role->label() }}</strong>@if($user->department) in <strong>{{ $user->department->name }}</strong>@endif.
        Review your details and create a password to activate your account.
    </x-slot:lead>

    <div class="rounded-2xl border border-maroon-900/10 bg-white p-6 sm:p-8">

        {{-- Account info badge --}}
        <div class="mb-5 flex items-center gap-3 rounded-xl bg-maroon-900/5 px-4 py-3">
            <span class="text-lg">✉</span>
            <p class="text-sm text-maroon-900/80">
                Account email: <strong>{{ $user->email }}</strong>
            </p>
        </div>

        <form method="POST" action="{{ route('invitation.store', $token) }}" class="space-y-4">
            @csrf

            {{-- Name — pre-filled from admin, still editable --}}
            <div>
                <label for="name" class="block text-sm font-medium text-maroon-900">Full name</label>
                <input id="name" name="name" type="text" required
                       value="{{ old('name', $user->name) }}"
                       placeholder="Lastname, Firstname, Middlename"
                       class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                @error('name') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>

            <x-password-field
                id="password"
                name="password"
                label="New password"
                placeholder="At least 8 characters" />
            @error('password') <p class="-mt-2 text-sm text-maroon-600">{{ $message }}</p> @enderror

            <x-password-field
                id="password_confirmation"
                name="password_confirmation"
                label="Confirm password"
                placeholder="Re-enter your password" />

            <button type="submit"
                    class="w-full rounded-xl bg-maroon-800 px-4 py-3.5 text-base font-semibold text-cream-50 hover:bg-maroon-700">
                Activate Account &amp; Log In
            </button>
        </form>
    </div>
</x-page-shell>
@endsection
