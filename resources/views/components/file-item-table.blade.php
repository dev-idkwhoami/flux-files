@props(['item', 'selected' => false, 'onSelect' => null, 'type' => 'file', 'showActions' => false, 'actions' => null])

@php
    $isFolder = $type === 'folder' || (isset($item->type) && $item->type === 'folder');
    $isSelected = $selected || (isset($item->selected) && $item->selected);
    $locale = app()->getLocale();
@endphp

<flux:table.row
    @if($onSelect)
        wire:click="{{ $onSelect }}({{ $item->id ?? '' }})"
    @endif
    @class([
        'border-l-4 border-white dark:border-zinc-800 cursor-pointer',
        'border-collapse border-l-4 border-l-blue-500 dark:border-l-blue-400' => $isSelected
    ])
>
    <flux:table.cell>
        <div class="flex items-center space-x-2">
            <div class="flex-shrink-0">
                @if($isFolder)
                    <x-flux-files::folder-icon class="size-5" />
                @else
                    <x-flux-files::file-icon :file="$item" class="size-5" />
                @endif
            </div>
            <div class="text-sm font-medium">
                {{ $item->name ?? $item->original_name ?? '' }}
            </div>
        </div>
    </flux:table.cell>

    <flux:table.cell>
        @if($isFolder)
            —
        @else
            <x-flux-files::file-size :bytes="$item->size ?? 0" />
        @endif
    </flux:table.cell>

    <flux:table.cell>
        @if($isFolder)
            Folder
        @else
            {{ ucfirst(explode('/', $item->mime_type ?? 'application/octet-stream')[0]) }}
        @endif
    </flux:table.cell>

    <flux:table.cell>
        <x-flux-files::file-date :date="$item->created_at ?? $item->updated_at ?? now()" />
    </flux:table.cell>

    @if($showActions)
        <flux:table.cell wire:click.stop>
            @if($actions)
                {{ $actions }}
            @elseif($isFolder)
                <x-flux-files::folder-actions-dropdown :folder="$item" />
            @else
                <x-flux-files::file-actions-dropdown :file="$item" />
            @endif
        </flux:table.cell>
    @endif
</flux:table.row>
