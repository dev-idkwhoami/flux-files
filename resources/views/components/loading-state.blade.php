@props(['message' => 'Loading...', 'showSpinner' => true, 'size' => 'md'])

@php
    $sizes = [
        'sm' => 'size-4',
        'md' => 'size-6',
        'lg' => 'size-8'
    ];
    $spinnerSize = $sizes[$size] ?? $sizes['md'];
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center justify-center space-x-2 py-8']) }}>
    @if($showSpinner)
        <flux:icon name="loading" :class="$spinnerSize . ' animate-spin text-blue-500'" />
    @endif

    @if($message)
        <flux:text class="text-gray-600 dark:text-gray-400">
            {{ $message }}
        </flux:text>
    @endif
</div>
