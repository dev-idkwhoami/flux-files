@props(['icon' => 'folder-open', 'title' => 'No files found', 'message' => 'This folder is empty. Upload files or create folders to get started.'])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center py-12 text-center']) }}>
    <flux:icon :name="$icon" class="size-16 text-gray-300 dark:text-gray-600 mb-4" />

    <flux:heading level="3" class="text-gray-600 dark:text-gray-400 mb-2">
        {{ $title }}
    </flux:heading>

    <flux:text class="text-gray-500 dark:text-gray-500 max-w-md">
        {{ $message }}
    </flux:text>

    @if(isset($actions))
        <div class="mt-6">
            {{ $actions }}
        </div>
    @endif
</div>
