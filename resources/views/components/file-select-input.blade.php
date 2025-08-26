@props(['selectedFile' => null, 'placeholder' => 'No file selected', 'onClear' => null, 'onSelect' => null])

<div {{ $attributes->merge(['class' => 'relative flex items-center']) }}>
    <div class="flex-1 min-w-0 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-l-md bg-white dark:bg-gray-800 text-sm">
        @if($selectedFile)
            <div class="flex items-center space-x-2">
                <x-flux-files::file-icon :file="$selectedFile" class="size-4" />
                <span class="truncate">{{ $selectedFile->original_name ?? $selectedFile->name ?? 'Selected file' }}</span>
            </div>
        @else
            <span class="text-gray-500 dark:text-gray-400">{{ $placeholder }}</span>
        @endif
    </div>

    <div class="flex border-t border-r border-b border-gray-300 dark:border-gray-600 rounded-r-md">
        @if($selectedFile && $onClear)
            <flux:button
                square
                size="sm"
                variant="ghost"
                icon="x-mark"
                wire:click="{{ $onClear }}"
                tooltip="Clear selection"
                class="rounded-none border-r border-gray-300 dark:border-gray-600"
            />
        @endif

        <flux:button
            square
            size="sm"
            variant="ghost"
            icon="folder-open"
            @if($onSelect)
                wire:click="{{ $onSelect }}"
            @endif
            tooltip="Browse files"
            class="rounded-l-none"
        />
    </div>
</div>
