@props(['viewMode' => 'grid', 'onToggle' => null])

<flux:button
    variant="filled"
    @if($onToggle)
        wire:click="{{ $onToggle }}"
    @endif
    :icon="$viewMode !== 'grid' ? 'layout-grid' : 'table'"
    tooltip="Toggle view mode"
    {{ $attributes }}
/>
