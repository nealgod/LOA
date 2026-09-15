@props([
    'id',
    'name',
    'label',
    'placeholder' => '',
])

<div>
    <label for="{{ $id }}" class="block text-sm font-medium text-maroon-900">{{ $label }}</label>
    <div class="relative mt-1" data-password-wrap>
        <input
            id="{{ $id }}"
            name="{{ $name }}"
            type="password"
            required
            data-password-input
            placeholder="{{ $placeholder }}"
            {{ $attributes->merge(['class' => 'w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 pr-20 outline-none ring-maroon-700 focus:ring-2']) }}
        >
        <button
            type="button"
            data-password-toggle
            class="absolute inset-y-0 right-2 my-auto h-8 rounded-lg px-2 text-sm font-semibold text-maroon-700 hover:bg-cream-100"
        >
            Show
        </button>
    </div>
    {{ $slot }}
</div>
