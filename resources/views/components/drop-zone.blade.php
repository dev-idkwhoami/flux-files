@props([
    'dragDrop' => true,
    'multiple' => true,
    'allowedTypes' => [],
    'maxFileSize' => null,
    'maxFiles' => null,
    'inputId' => 'file-upload-input',
    'onFileSelect' => 'handleFileSelection'
])

<div
    @if($dragDrop)
        x-data="{ dragOver: false }"
        @dragover.prevent="dragOver = true"
        @dragleave.prevent="dragOver = false"
        @drop.prevent="
            dragOver = false;
            let files = Array.from($event.dataTransfer.files);
            window.{{ $onFileSelect }}(files);
        "
        :class="{
            'border-gray-500 bg-gray-50 dark:bg-gray-900/20': dragOver,
            'border-gray-300 dark:border-gray-600': !dragOver
        }"
    @endif
    {{ $attributes->merge(['class' => 'relative border-2 border-dashed rounded-lg p-6 text-center transition-colors duration-200']) }}
>
    <div class="space-y-4">
        <flux:icon.upload class="mx-auto h-12 w-12 text-gray-400"/>

        <div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                @if($dragDrop)
                    Drag and drop files here
                @else
                    Select files to upload
                @endif
            </h3>

            @if($dragDrop)
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    or click to browse
                </p>
            @endif

            {{-- File Restrictions --}}
            <x-flux-files::file-restrictions
                :allowedTypes="$allowedTypes"
                :maxFileSize="$maxFileSize"
                :maxFiles="$maxFiles"
                :multiple="$multiple"
            />
        </div>

        <flux:button
            type="button"
            variant="primary"
            onclick="document.getElementById('{{ $inputId }}').click()"
        >
            Browse Files
        </flux:button>
    </div>

    {{-- Hidden File Input --}}
    <input
        id="{{ $inputId }}"
        type="file"
        @change="window.{{ $onFileSelect }}($event.target.files)"
        @if($multiple) multiple @endif
        @if(!empty($allowedTypes))
            accept="{{ implode(',', array_map(fn($ext) => '.' . $ext, $allowedTypes)) }}"
        @endif
        class="hidden"
    />
</div>
