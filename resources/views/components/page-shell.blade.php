@props([
    'eyebrow' => null,
    'title',
    'width' => 'xl',
])

@php
    $maxWidth = [
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
    ][$width] ?? 'max-w-xl';
@endphp

<section class="flex flex-1 items-center">
    <div class="mx-auto w-full max-w-7xl px-4 py-12 sm:px-8 lg:px-10 lg:py-16">
        <div class="mx-auto w-full {{ $maxWidth }}">
            @if ($eyebrow)
                <p class="text-sm font-semibold uppercase tracking-[0.22em] text-maroon-600">{{ $eyebrow }}</p>
            @endif
            <h1 class="{{ $eyebrow ? 'mt-2' : '' }} text-3xl font-semibold text-maroon-900 lg:text-4xl">{{ $title }}</h1>
            @isset($lead)
                <div class="mt-3 text-base leading-relaxed text-maroon-800/80">{{ $lead }}</div>
            @endisset
            <div class="mt-8">
                {{ $slot }}
            </div>
        </div>
    </div>
</section>
