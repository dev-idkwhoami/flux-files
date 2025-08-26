@props(['file', 'index', 'errors' => [], 'onRemove' => null, 'showPreview' => true, 'isUploading' => false])

<div {{ $attributes->merge(['class' => 'flex items-center space-x-3 p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg']) }}>
    {{-- File Preview --}}
    @if($showPreview)
        <div class="flex-shrink-0">
            <x-flux-files::file-preview :file="$file" size="sm" />
        </div>
    @endif

    {{-- File Info --}}
    <div class="flex-1 min-w-0">
        <div class="flex items-center justify-between">
            <div class="truncate">
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                    {{ $file->getClientOriginalName() ?? $file->original_name ?? $file->name ?? 'Unknown file' }}
                </p>
                <div class="flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400">
                    <x-flux-files::file-size :bytes="$file->getSize() ?? $file->size ?? 0" />
                    @if(method_exists($file, 'getMimeType') || isset($file->mime_type))
                        <span>•</span>
                        <span>{{ $file->getMimeType() ?? $file->mime_type ?? 'Unknown' }}</span>
                    @endif
                </div>
            </div>

            {{-- Upload Status --}}
            @if($isUploading)
                <flux:icon name="loading" class="size-4 animate-spin text-blue-500" />
            @endif
        </div>

        {{-- Validation Errors --}}
        @if(!empty($errors))
            <div class="mt-2 space-y-1">
                @foreach($errors as $error)
                    <flux:error class="text-xs">{{ $error }}</flux:error>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Remove Button --}}
    @if($onRemove && !$isUploading)
        <flux:button
            square
            size="xs"
            variant="ghost"
            icon="x-mark"
            wire:click="{{ $onRemove }}({{ $index }})"
            tooltip="Remove file"
            class="flex-shrink-0"
        />
    @endif
</div>
