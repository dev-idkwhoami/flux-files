@props(['successCount' => 0, 'errorCount' => 0, 'totalCount' => null])

@php
    $total = $totalCount ?? ($successCount + $errorCount);
@endphp

@if($total > 0)
    <div {{ $attributes->merge(['class' => 'p-4 rounded-lg border']) }}>
        <div class="flex items-center justify-between">
            <flux:heading level="5">Upload Summary</flux:heading>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                {{ $total }} {{ Str::plural('file', $total) }} processed
            </div>
        </div>

        <div class="mt-3 space-y-2">
            @if($successCount > 0)
                <div class="flex items-center space-x-2 text-green-600 dark:text-green-400">
                    <flux:icon name="check-circle" class="size-4" />
                    <span class="text-sm">{{ $successCount }} {{ Str::plural('file', $successCount) }} uploaded successfully</span>
                </div>
            @endif

            @if($errorCount > 0)
                <div class="flex items-center space-x-2 text-red-600 dark:text-red-400">
                    <flux:icon name="x-circle" class="size-4" />
                    <span class="text-sm">{{ $errorCount }} {{ Str::plural('file', $errorCount) }} failed to upload</span>
                </div>
            @endif
        </div>

        @if($total > 0)
            <div class="mt-3">
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    @if($successCount > 0)
                        <div
                            class="bg-green-500 h-2 rounded-full"
                            style="width: {{ ($successCount / $total) * 100 }}%"
                        ></div>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endif
