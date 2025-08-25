<div
    x-data="{
        dragOver: false,
        uploading: @entangle('isUploading'),
        files: @entangle('files'),
        errors: @entangle('validationErrors'),
        completed: @entangle('completedUploads'),
    }"
    class="w-full"
>
    {{-- Main Upload Zone --}}
    <div
        @if($this->dragDrop)
            @dragover.prevent="dragOver = true"
        @dragleave.prevent="dragOver = false"
        @drop.prevent="
            dragOver = false;
            let files = Array.from($event.dataTransfer.files);
            window.handleFileSelection(files);
        "
        @endif
        :class="{
            'border-gray-500 bg-gray-50 dark:bg-gray-900/20': dragOver,
            'border-gray-300 dark:border-gray-600': !dragOver
        }"
        @class(['relative border-2 border-dashed rounded-lg p-6 text-center transition-colors duration-200' => $this->multiple || empty($this->files)])
    >
        @if($this->multiple || empty($this->files))
            {{-- Upload Instructions --}}
            <div class="space-y-4">
                <flux:icon.upload class="mx-auto h-12 w-12 text-gray-400"/>

                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        @if($this->dragDrop)
                            Drag and drop files here
                        @else
                            Select files to upload
                        @endif
                    </h3>

                    @if($this->dragDrop)
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            or click to browse
                        </p>
                    @endif

                    {{-- File Restrictions --}}
                    @if(!empty($this->allowedTypes) || $this->maxFileSize || $this->maxFiles)
                        <div class="mt-3 text-xs text-gray-400 space-y-1">
                            @if(!empty($this->allowedTypes))
                                <div>Allowed types: {{ implode(', ', $this->allowedTypes) }}</div>
                            @endif
                            @if($this->maxFileSize)
                                <div>File Size:
                                    < {{ \Idkwhoami\FluxFiles\Support\HumanReadable::formatBytes($this->maxFileSize) }} </div>
                            @endif
                            @if($this->maxFiles && $this->multiple)
                                <div>Max files: {{ $this->maxFiles }}</div>
                            @endif
                        </div>
                    @endif
                </div>

                <flux:button
                    type="button"
                    variant="primary"
                    onclick="document.getElementById('file-upload-input-{{ $this->getId() }}').click()"
                >
                    Browse Files
                </flux:button>
            </div>
        @endif

        {{-- File Input --}}
        <input
            id="file-upload-input-{{ $this->getId() }}"
            type="file"
            @change="window.handleFileSelection($event.target.files)"
            @if($this->multiple) multiple @endif
            @if(!empty($this->allowedTypes)) accept="{{ implode(',', array_map(fn($ext) => '.' . $ext, $this->allowedTypes)) }}"
            @endif
            class="hidden"
        />
    </div>

    {{-- Global Validation Errors --}}
    @if(isset($this->validationErrors['max_files']))
        <flux:error class="mt-2">
            {{ $this->validationErrors['max_files'] }}
        </flux:error>
    @endif

    {{-- Individual File Validation Errors (shown even when files don't display) --}}
    @php
        $individualErrors = array_filter($this->validationErrors, function($key) {
            return is_numeric($key);
        }, ARRAY_FILTER_USE_KEY);
    @endphp

    @if(!empty($individualErrors))
        <div class="mt-2 space-y-2">
            @foreach($individualErrors as $index => $errors)
                @if(is_array($errors))
                    @foreach($errors as $error)
                        <flux:error class="text-sm">
                            @if(isset($this->files[$index]))
                                {{ $this->files[$index]->getClientOriginalName() }}: {{ $error }}
                            @else
                                File {{ $index + 1 }}: {{ $error }}
                            @endif
                        </flux:error>
                    @endforeach
                @else
                    <flux:error class="text-sm">
                        @if(isset($this->files[$index]))
                            {{ $this->files[$index]->getClientOriginalName() }}: {{ $errors }}
                        @else
                            File {{ $index + 1 }}: {{ $errors }}
                        @endif
                    </flux:error>
                @endif
            @endforeach
        </div>
    @endif

    {{-- File List --}}
    @if(!empty($this->files))
        <div class="mt-6 space-y-4">
            @if($this->multiple || empty($this->files))
                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        Files ({{ count($this->files) }})
                    </h4>

                    @if(!$this->isUploading)
                        <flux:button
                            type="button"
                            variant="ghost"
                            size="sm"
                            wire:click="clearAll"
                        >
                            Clear All
                        </flux:button>
                    @endif
                </div>
            @endif

            <div class="space-y-3">
                @foreach($this->files as $index => $file)
                    <div class="flex items-center space-x-4 border-1 border-gray-600 p-3 rounded-lg">
                        {{-- File Preview/Icon --}}
                        <div class="flex-shrink-0">
                            @if($this->showPreviews && $file->isPreviewable())
                                <img
                                    src="{{ $file->temporaryUrl() }}"
                                    alt="Preview"
                                    class="w-12 h-12 object-cover rounded"
                                />
                            @else
                                <div
                                    class="w-12 h-12 flex items-center justify-center bg-gray-200 dark:bg-gray-700 rounded">
                                    <flux:icon name="{{ $this->getFileIcon($file) }}" class="w-6 h-6 text-gray-500"/>
                                </div>
                            @endif
                        </div>

                        {{-- File Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                {{ $file->getClientOriginalName() }}
                            </div>

                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ \Idkwhoami\FluxFiles\Support\HumanReadable::formatBytes($file->getSize()) }}
                            </div>


                            {{-- Validation Errors --}}
                            @if(isset($this->validationErrors[$index]))
                                <div class="mt-2 space-y-1">
                                    @foreach($this->validationErrors[$index] as $error)
                                        <flux:error class="text-xs">{{ $error }}</flux:error>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Success State --}}
                            @if(isset($this->completedUploads[$index]) || $file instanceof Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
                                <div class="mt-2 flex items-center text-xs text-green-600 dark:text-green-400">
                                    <flux:icon.check class="w-3 h-3 mr-1"/>
                                    Upload completed
                                </div>
                            @endif
                        </div>

                        {{-- Actions --}}
                        @if(!$this->isUploading || isset($this->validationErrors[$index]))
                            <div class="flex-shrink-0">
                                <flux:button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    wire:click="removeFile({{ $index }})"
                                    class="text-red-600 hover:text-red-700"
                                >
                                    <flux:icon.x-mark class="w-4 h-4"/>
                                </flux:button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Upload Summary --}}
            @if($this->isUploading)
                <div class="mt-4 p-4 bg-zinc-50 dark:bg-zinc-900/20 rounded-lg">
                    <div class="flex items-center">
                        <flux:icon.arrow-path class="animate-spin w-5 h-5 text-zinc-600 mr-2"/>
                        <span class="text-sm text-zinc-900 dark:text-zinc-100">
                            Uploading {{ count($this->files) }} file(s)...
                        </span>
                    </div>
                </div>
            @elseif(!empty($this->completedUploads))
                <div class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                    <div class="flex items-center">
                        <flux:icon.check-circle class="w-5 h-5 text-green-600 mr-2"/>
                        <span class="text-sm text-green-900 dark:text-green-100">
                            {{ count($this->completedUploads) }} file(s) uploaded successfully
                        </span>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>

<script>
    document.addEventListener('livewire:init', () => {
        // Simple file selection handler for regular uploads
        window.handleFileSelection = function (files) {
            const fileArray = Array.from(files);
            const currentFilesCount = @this.
            get('files').length;

            // Add each file individually to append to the array
            for (let i = 0; i < fileArray.length; i++) {
                const targetIndex = currentFilesCount + i;
                @this.
                upload(`files.${targetIndex}`, fileArray[i]);
            }
        };

        // Listen for upload events
        Livewire.on('upload-started', (event) => {
            console.debug(`Upload started for ${event.fileCount} files`);
        });

        Livewire.on('file-uploaded', (event) => {
            console.debug(`File ${event.index} uploaded with ID: ${event.fileId}`);
        });

        Livewire.on('file-upload-failed', (event) => {
            console.error(`Upload failed for file ${event.index}: ${event.message}`);
        });

        Livewire.on('upload-completed', (event) => {
            console.debug(`Upload completed. ${event.successCount} files uploaded successfully`);
        });
    });
</script>
