@extends('layouts.app')

@section('title', 'Request submitted')

@section('content')
<x-page-shell eyebrow="Submitted" title="Your Control Number">
    <div class="rounded-2xl border border-maroon-900/10 bg-white p-6 sm:p-8">
        <p class="rounded-xl bg-maroon-900 px-4 py-4 text-center font-mono text-lg tracking-wide text-gold-400">
            {{ $loa->control_number }}
        </p>
        <p class="mt-4 text-base leading-relaxed text-maroon-800/80">
            Keep this number. Your Department Head will review next, including the parent/guardian verification call.
            A confirmation email path will be completed when outbound mail is configured.
        </p>
        <a href="{{ route('home') }}" class="mt-8 inline-flex text-sm font-semibold text-maroon-700">Back to home</a>
    </div>
</x-page-shell>
@endsection
