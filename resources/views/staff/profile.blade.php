@extends('layouts.staff')

@section('eyebrow', 'My Account')
@section('title', 'User Profile')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">

    {{-- ── Hero card ──────────────────────────────────────────────────────── --}}
    <div class="overflow-hidden rounded-2xl border border-maroon-900/10 bg-white shadow-sm">

        {{-- Banner + avatar --}}
        <div class="relative h-24 bg-gradient-to-r from-maroon-900 via-maroon-800 to-maroon-700">
            <div class="absolute -bottom-8 left-6">
                @php
                    $nameParts = explode(' ', trim($user->name ?? 'U'));
                    $initials  = strtoupper(($nameParts[0][0] ?? 'U') . (end($nameParts)[0] ?? ''));
                @endphp
                <div class="flex h-16 w-16 items-center justify-center rounded-full border-4 border-white bg-maroon-600 text-xl font-bold uppercase tracking-wide text-cream-50 shadow-md">
                    {{ $initials }}
                </div>
            </div>
        </div>

        {{-- Name + role --}}
        <div class="px-6 pb-6 pt-12">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-maroon-950">{{ $user->name }}</h2>
                    <p class="mt-0.5 text-sm text-maroon-900/60">{{ $user->email }}</p>
                </div>
                {{-- Role badge --}}
                @php
                    $roleColor = match ($user->role) {
                        \App\Enums\UserRole::Administrator    => 'bg-purple-100 text-purple-800 border-purple-300',
                        \App\Enums\UserRole::DepartmentHead   => 'bg-blue-100 text-blue-800 border-blue-300',
                        \App\Enums\UserRole::SasoOfficer      => 'bg-amber-100 text-amber-800 border-amber-300',
                        \App\Enums\UserRole::CampusDirector   => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                        \App\Enums\UserRole::Registrar        => 'bg-teal-100 text-teal-800 border-teal-300',
                        \App\Enums\UserRole::Guidance         => 'bg-sky-100 text-sky-800 border-sky-300',
                        default                               => 'bg-gray-100 text-gray-700 border-gray-300',
                    };
                @endphp
                <span class="rounded-full border px-3 py-1 text-xs font-semibold {{ $roleColor }}">
                    {{ $user->role->label() }}
                </span>
            </div>
        </div>
    </div>

    {{-- ── Account details ─────────────────────────────────────────────────── --}}
    <div class="rounded-2xl border border-maroon-900/10 bg-white shadow-sm">
        <div class="border-b border-maroon-900/8 px-6 py-4">
            <h3 class="text-sm font-semibold text-maroon-950">Account Details</h3>
        </div>
        <dl class="divide-y divide-maroon-900/8 px-6">
            <div class="flex items-center justify-between py-3.5">
                <dt class="text-sm text-maroon-900/60">Full name</dt>
                <dd class="text-sm font-medium text-maroon-950">{{ $user->name }}</dd>
            </div>
            <div class="flex items-center justify-between py-3.5">
                <dt class="text-sm text-maroon-900/60">Email address</dt>
                <dd class="text-sm font-medium text-maroon-950">{{ $user->email }}</dd>
            </div>
            <div class="flex items-center justify-between py-3.5">
                <dt class="text-sm text-maroon-900/60">Role</dt>
                <dd class="text-sm font-medium text-maroon-950">{{ $user->role->label() }}</dd>
            </div>
            <div class="flex items-center justify-between py-3.5">
                <dt class="text-sm text-maroon-900/60">Department</dt>
                <dd class="text-sm font-medium text-maroon-950">
                    {{ $user->department?->name ?? '— Campus-wide' }}
                </dd>
            </div>
            <div class="flex items-center justify-between py-3.5">
                <dt class="text-sm text-maroon-900/60">Account created</dt>
                <dd class="text-sm font-medium text-maroon-950">
                    {{ $user->created_at?->format('F j, Y') ?? '—' }}
                </dd>
            </div>
        </dl>
    </div>

    {{-- ── Activity stats ───────────────────────────────────────────────────── --}}
    <div class="rounded-2xl border border-maroon-900/10 bg-white shadow-sm">
        <div class="border-b border-maroon-900/8 px-6 py-4">
            <h3 class="text-sm font-semibold text-maroon-950">Your Activity</h3>
            <p class="mt-0.5 text-xs text-maroon-900/50">LOA requests you have personally acted on.</p>
        </div>
        <div class="grid grid-cols-3 divide-x divide-maroon-900/8 px-0">
            <div class="px-6 py-5 text-center">
                <p class="text-3xl font-bold text-maroon-950">{{ $acted }}</p>
                <p class="mt-1 text-xs font-medium text-maroon-900/60">Total Acted</p>
            </div>
            <div class="px-6 py-5 text-center">
                <p class="text-3xl font-bold text-emerald-700">{{ $approved }}</p>
                <p class="mt-1 text-xs font-medium text-maroon-900/60">Approved</p>
            </div>
            <div class="px-6 py-5 text-center">
                <p class="text-3xl font-bold text-red-600">{{ $rejected }}</p>
                <p class="mt-1 text-xs font-medium text-maroon-900/60">Rejected</p>
            </div>
        </div>
    </div>

    {{-- ── Pending actions ──────────────────────────────────────────────────── --}}
    <div class="rounded-2xl border border-dashed border-maroon-900/15 bg-white p-6">
        <h3 class="mb-1 text-sm font-semibold text-maroon-950">Account actions coming soon</h3>
        <p class="text-sm text-maroon-900/60 mb-5">
            Profile editing, password change, notification preferences, and two-factor authentication will be available in a later release.
        </p>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('staff.pipeline') }}"
               class="rounded-lg bg-maroon-700 px-4 py-2 text-sm font-semibold text-cream-50 shadow-sm hover:bg-maroon-600">
                Go to Pipeline
            </a>
            <a href="{{ route('staff.dashboard') }}"
               class="rounded-lg border border-maroon-900/20 bg-white px-4 py-2 text-sm font-semibold text-maroon-700 hover:bg-maroon-50">
                Back to Dashboard
            </a>
        </div>
    </div>

</div>
@endsection
