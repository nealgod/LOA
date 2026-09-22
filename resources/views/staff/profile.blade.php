@extends('layouts.staff')

@section('eyebrow', 'My Account')
@section('title', 'User Profile')

@section('content')
@php
    $nameParts = explode(' ', trim($user->name ?? 'U'));
    $initials  = strtoupper(($nameParts[0][0] ?? 'U') . (end($nameParts)[0] ?? ''));
    $roleColor = match ($user->role) {
        \App\Enums\UserRole::Administrator  => 'bg-purple-100 text-purple-800 border-purple-300',
        \App\Enums\UserRole::DepartmentHead => 'bg-blue-100 text-blue-800 border-blue-300',
        \App\Enums\UserRole::SasoOfficer    => 'bg-amber-100 text-amber-800 border-amber-300',
        \App\Enums\UserRole::CampusDirector => 'bg-emerald-100 text-emerald-800 border-emerald-300',
        \App\Enums\UserRole::Registrar      => 'bg-teal-100 text-teal-800 border-teal-300',
        \App\Enums\UserRole::Guidance       => 'bg-sky-100 text-sky-800 border-sky-300',
        default                             => 'bg-gray-100 text-gray-700 border-gray-300',
    };
@endphp

<div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

    {{-- ── Left column: identity + activity ───────────────────────────────── --}}
    <div class="space-y-5">

        {{-- Identity card --}}
        <div class="overflow-hidden rounded-2xl border border-maroon-900/10 bg-white shadow-sm">
            {{-- Gradient banner --}}
            <div class="relative h-20 bg-gradient-to-r from-maroon-900 via-maroon-800 to-maroon-700">
                <div class="absolute -bottom-7 left-5">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full border-4 border-white bg-maroon-600 text-lg font-bold uppercase text-cream-50 shadow-md">
                        {{ $initials }}
                    </div>
                </div>
            </div>
            <div class="px-5 pb-5 pt-10">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="truncate text-base font-bold text-maroon-950">{{ $user->name }}</p>
                        <p class="truncate text-xs text-maroon-900/55">{{ $user->email }}</p>
                    </div>
                    <span class="shrink-0 rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $roleColor }}">
                        {{ $user->role->label() }}
                    </span>
                </div>

                <dl class="mt-4 space-y-2 border-t border-maroon-900/8 pt-4 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-xs text-maroon-900/50">Department</dt>
                        <dd class="text-xs font-medium text-maroon-950">{{ $user->department?->name ?? 'Campus-wide' }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-xs text-maroon-900/50">Member since</dt>
                        <dd class="text-xs font-medium text-maroon-950">{{ $user->created_at?->format('M j, Y') ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Activity stats --}}
        <div class="rounded-2xl border border-maroon-900/10 bg-white shadow-sm">
            <div class="border-b border-maroon-900/8 px-5 py-3">
                <p class="text-xs font-semibold uppercase tracking-wider text-maroon-900/60">Your Activity</p>
            </div>
            <div class="grid grid-cols-3 divide-x divide-maroon-900/8">
                <div class="py-4 text-center">
                    <p class="text-2xl font-bold text-maroon-950">{{ $acted }}</p>
                    <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wider text-maroon-900/50">Acted</p>
                </div>
                <div class="py-4 text-center">
                    <p class="text-2xl font-bold text-emerald-700">{{ $approved }}</p>
                    <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wider text-maroon-900/50">Approved</p>
                </div>
                <div class="py-4 text-center">
                    <p class="text-2xl font-bold text-red-600">{{ $rejected }}</p>
                    <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wider text-maroon-900/50">Rejected</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Right column: edit name + change password ───────────────────────── --}}
    <div class="space-y-5 lg:col-span-2">

        {{-- Edit name --}}
        <div class="rounded-2xl border border-maroon-900/10 bg-white shadow-sm">
            <div class="border-b border-maroon-900/8 px-5 py-3.5">
                <p class="text-sm font-semibold text-maroon-950">Edit Name</p>
                <p class="mt-0.5 text-xs text-maroon-900/50">Your email and role are managed by the administrator.</p>
            </div>
            <div class="px-5 py-4">
                @if (session('profile_status'))
                    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-700">
                        {{ session('profile_status') }}
                    </div>
                @endif
                <form method="POST" action="{{ route('staff.profile.name') }}">
                    @csrf
                    @method('PATCH')
                    <div class="flex items-start gap-3">
                        <div class="flex-1">
                            <label for="name" class="block text-xs font-medium text-maroon-900 mb-1">Full name</label>
                            <input id="name" type="text" name="name"
                                   value="{{ old('name', $user->name) }}"
                                   required autocomplete="name"
                                   class="w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-2.5 text-sm text-maroon-950 outline-none focus:ring-2 focus:ring-maroon-700 @error('name') border-red-400 bg-red-50 @enderror">
                            @error('name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="pt-5">
                            <button type="submit"
                                    class="rounded-xl bg-maroon-800 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-maroon-700 whitespace-nowrap">
                                Save
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Change password --}}
        <div class="rounded-2xl border border-maroon-900/10 bg-white shadow-sm">
            <div class="border-b border-maroon-900/8 px-5 py-3.5">
                <p class="text-sm font-semibold text-maroon-950">Change Password</p>
                <p class="mt-0.5 text-xs text-maroon-900/50">Must be at least 8 characters.</p>
            </div>
            <div class="px-5 py-4">
                @if (session('password_status'))
                    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-700">
                        {{ session('password_status') }}
                    </div>
                @endif
                <form method="POST" action="{{ route('staff.profile.password') }}">
                    @csrf
                    @method('PATCH')
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

                        {{-- Current password --}}
                        <div>
                            <label for="current_password" class="block text-xs font-medium text-maroon-900 mb-1">Current password</label>
                            <div class="relative">
                                <input id="current_password" type="password" name="current_password"
                                       required autocomplete="current-password"
                                       class="w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-2.5 pr-10 text-sm text-maroon-950 outline-none focus:ring-2 focus:ring-maroon-700 @error('current_password') border-red-400 bg-red-50 @enderror">
                                <button type="button" tabindex="-1"
                                        onclick="togglePassword('current_password', this)"
                                        class="absolute inset-y-0 right-0 flex items-center px-3 text-maroon-900/40 hover:text-maroon-700">
                                    <svg class="eye-icon h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                </button>
                            </div>
                            @error('current_password')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- New password --}}
                        <div>
                            <label for="password" class="block text-xs font-medium text-maroon-900 mb-1">New password</label>
                            <div class="relative">
                                <input id="password" type="password" name="password"
                                       required autocomplete="new-password"
                                       class="w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-2.5 pr-10 text-sm text-maroon-950 outline-none focus:ring-2 focus:ring-maroon-700 @error('password') border-red-400 bg-red-50 @enderror">
                                <button type="button" tabindex="-1"
                                        onclick="togglePassword('password', this)"
                                        class="absolute inset-y-0 right-0 flex items-center px-3 text-maroon-900/40 hover:text-maroon-700">
                                    <svg class="eye-icon h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Confirm new password --}}
                        <div>
                            <label for="password_confirmation" class="block text-xs font-medium text-maroon-900 mb-1">Confirm new password</label>
                            <div class="relative">
                                <input id="password_confirmation" type="password" name="password_confirmation"
                                       required autocomplete="new-password"
                                       class="w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-2.5 pr-10 text-sm text-maroon-950 outline-none focus:ring-2 focus:ring-maroon-700">
                                <button type="button" tabindex="-1"
                                        onclick="togglePassword('password_confirmation', this)"
                                        class="absolute inset-y-0 right-0 flex items-center px-3 text-maroon-900/40 hover:text-maroon-700">
                                    <svg class="eye-icon h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button type="submit"
                                class="rounded-xl bg-maroon-800 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-maroon-700">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function togglePassword(fieldId, btn) {
    const input = document.getElementById(fieldId);
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';

    // Swap icon: eye ↔ eye-slash
    btn.querySelector('.eye-icon').innerHTML = isHidden
        ? '<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/>'
        : '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>';
}
</script>
@endpush
