@extends('layouts.app')

@section('title', 'Check your email')

@section('content')
<x-page-shell eyebrow="Link sent" title="Open your email">
    <x-slot:lead>
        We sent a private LOA form link to <strong>{{ session('email') }}</strong>.
        Open that message and use the button to continue. The link expires in 24 hours.
    </x-slot:lead>

    <div class="rounded-2xl border border-maroon-900/10 bg-white p-6 sm:p-8">
        <p class="text-base leading-relaxed text-maroon-800/80">
            Check your inbox and spam folder. You can use the link on this phone or another device.
        </p>
        <a href="{{ route('home') }}" class="mt-6 inline-flex text-sm font-semibold text-maroon-700">Back to home</a>
    </div>
</x-page-shell>
@endsection
