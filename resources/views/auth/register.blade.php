{{--
    Self-registration has been removed.
    Staff accounts are created by the System Administrator via invitation email.
    This file is kept to prevent 404 errors on cached links.
--}}
@extends('layouts.app')

@section('title', 'Staff registration unavailable')

@section('content')
<x-page-shell eyebrow="Staff" title="Registration unavailable">
    <x-slot:lead>
        Self-registration has been disabled. Staff accounts are created by the System Administrator.
    </x-slot:lead>

    <div class="rounded-2xl border border-maroon-900/10 bg-white p-6 sm:p-8">
        <p class="text-sm text-maroon-800/80 mb-5">
            If you have been invited, check your email for a setup link from <strong>EVSU OCC LOA</strong>.
            If you need an account, contact the System Administrator.
        </p>
        <a href="{{ route('login') }}"
           class="inline-flex rounded-xl bg-maroon-800 px-5 py-2.5 text-sm font-semibold text-cream-50 hover:bg-maroon-700">
            Go to Staff Login
        </a>
    </div>
</x-page-shell>
@endsection
