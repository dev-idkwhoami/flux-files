@props(['isUploading' => false, 'progress' => 0, 'message' => 'Uploading...', 'showSpinner' => true])

@if($isUploading)
    <div {{ $attributes->merge(['class' => 'flex items-center space-x-2']) }}>
        @if($showSpinner)
            <flux:icon name="loading" class="size-4 animate-spin" />
        @endif

        <div class="flex-1">
            <div class="text-sm text-gray-600 dark:text-gray-400">
                {{ $message }}
            </div>

            @if($progress > 0)
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mt-1">
                    <div
                        class="bg-blue-500 h-2 rounded-full transition-all duration-300"
                        style="width: {{ $progress }}%"
                    ></div>
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ $progress }}%
                </div>
            @endif
        </div>
    </div>
@endif
