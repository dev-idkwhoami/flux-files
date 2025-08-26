@props(['item', 'selected' => false, 'showTooltip' => true, 'onSelect' => null, 'type' => 'file'])

@php
    $isFolder = $type === 'folder' || (isset($item->type) && $item->type === 'folder');
    $isSelected = $selected || (isset($item->selected) && $item->selected);
@endphp

<div
    @if($onSelect)
        wire:click="{{ $onSelect }}({{ $item->id ?? '' }})"
    @endif
    @class([
        'flex flex-col items-center p-3 hover:bg-neutral-100 dark:hover:bg-neutral-700 rounded-lg cursor-pointer transition-colors group relative border-2 border-transparent',
        'border-blue-500 dark:border-blue-400' => $isSelected
    ])
>
    @if($showTooltip && !$isFolder)
        <x-flux-files::file-tooltip :file="$item" class="absolute isolate top-0 right-0" />
    @endif

    <div class="mb-2">
        @if($isFolder)
            <x-flux-files::folder-icon />
        @else
            <x-flux-files::file-icon :file="$item" />
        @endif
    </div>

    <flux:text class="inline-flex text-center text-wrap truncate">
        {{ $item->name ?? $item->original_name ?? '' }}
    </flux:text>
</div>
