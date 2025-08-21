<div
    x-data="{
        dragOver: false,
        uploading: @entangle('isUploading'),
        files: @entangle('files'),
        progress: @entangle('uploadProgress'),
        errors: @entangle('validationErrors'),
        completed: @entangle('completedUploads')
    }"
    class="w-full"
>
    {{-- Main Upload Zone --}}
    <div
        @if($dragDrop)
        @dragover.prevent="dragOver = true"
        @dragleave.prevent="dragOver = false"
        @drop.prevent="
            dragOver = false;
            let files = Array.from($event.dataTransfer.files);
            window.handleFileSelection(files);
        "
        @endif
        :class="{
            'border-blue-500 bg-blue-50 dark:bg-blue-900/20': dragOver,
            'border-gray-300 dark:border-gray-600': !dragOver
        }"
        class="relative border-2 border-dashed rounded-lg p-6 text-center transition-colors duration-200"
    >
        @if(!$isUploading && empty($files))
            {{-- Empty State --}}
            <div class="space-y-4">
                <flux:icon.cloud-arrow-up class="mx-auto h-12 w-12 text-gray-400" />

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
                    @if(!empty($allowedTypes) || $maxFileSize || $maxFiles)
                        <div class="mt-3 text-xs text-gray-400 space-y-1">
                            @if(!empty($allowedTypes))
                                <div>Allowed types: {{ implode(', ', $allowedTypes) }}</div>
                            @endif
                            @if($maxFileSize)
                                <div>Max size: {{ number_format($maxFileSize / 1024 / 1024, 1) }}MB</div>
                            @endif
                            @if($maxFiles)
                                <div>Max files: {{ $maxFiles }}</div>
                            @endif
                        </div>
                    @endif
                </div>

                <flux:button
                    type="button"
                    variant="primary"
                    onclick="document.getElementById('file-input-{{ $this->getId() }}').click()"
                >
                    Browse Files
                </flux:button>
            </div>
        @endif

        {{-- File Input --}}
        <input
            id="file-input-{{ $this->getId() }}"
            type="file"
            @change="window.handleFileSelection($event.target.files)"
            @if($multiple) multiple @endif
            @if(!empty($allowedTypes)) accept="{{ implode(',', array_map(fn($ext) => '.' . $ext, $allowedTypes)) }}" @endif
            class="hidden"
        />
    </div>

    {{-- Global Validation Errors --}}
    @if(isset($validationErrors['max_files']))
        <flux:error class="mt-2">
            {{ $validationErrors['max_files'] }}
        </flux:error>
    @endif

    {{-- File List --}}
    @if(!empty($files))
        <div class="mt-6 space-y-4">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    Files ({{ count($files) }})
                </h4>

                @if(!$isUploading)
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

            <div class="space-y-3">
                @foreach($files as $index => $file)
                    <div class="flex items-center space-x-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        {{-- File Preview/Icon --}}
                        <div class="flex-shrink-0">
                            @if($showPreviews && $this->isPreviewable($file))
                                @if($this->getPreviewUrl($file))
                                    <img
                                        src="{{ $this->getPreviewUrl($file) }}"
                                        alt="Preview"
                                        class="w-12 h-12 object-cover rounded"
                                    />
                                @else
                                    <div class="w-12 h-12 flex items-center justify-center bg-gray-200 dark:bg-gray-700 rounded">
                                        <flux:icon name="{{ $this->getFileIcon($file) }}" class="w-6 h-6 text-gray-500" />
                                    </div>
                                @endif
                            @else
                                <div class="w-12 h-12 flex items-center justify-center bg-gray-200 dark:bg-gray-700 rounded">
                                    <flux:icon name="{{ $this->getFileIcon($file) }}" class="w-6 h-6 text-gray-500" />
                                </div>
                            @endif
                        </div>

                        {{-- File Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                {{ $file->getClientOriginalName() }}
                            </div>

                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ number_format($file->getSize() / 1024, 1) }} KB
                            </div>

                            {{-- Progress Bar --}}
                            @if($isUploading && isset($uploadProgress[$index]))
                                <div class="mt-2">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-gray-500">Uploading...</span>
                                        <span class="text-gray-900 dark:text-gray-100">{{ $uploadProgress[$index] }}%</span>
                                    </div>
                                    <div class="mt-1 w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                                        <div
                                            class="bg-blue-600 h-1.5 rounded-full transition-all duration-300"
                                            style="width: {{ $uploadProgress[$index] }}%"
                                        ></div>
                                    </div>
                                </div>
                            @endif

                            {{-- Validation Errors --}}
                            @if(isset($validationErrors[$index]))
                                <div class="mt-2 space-y-1">
                                    @foreach($validationErrors[$index] as $error)
                                        <flux:error class="text-xs">{{ $error }}</flux:error>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Success State --}}
                            @if(isset($completedUploads[$index]))
                                <div class="mt-2 flex items-center text-xs text-green-600 dark:text-green-400">
                                    <flux:icon.check class="w-3 h-3 mr-1" />
                                    Upload completed
                                </div>
                            @endif
                        </div>

                        {{-- Actions --}}
                        @if(!$isUploading || isset($validationErrors[$index]))
                            <div class="flex-shrink-0">
                                <flux:button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    wire:click="removeFile({{ $index }})"
                                    class="text-red-600 hover:text-red-700"
                                >
                                    <flux:icon.x-mark class="w-4 h-4" />
                                </flux:button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Upload Summary --}}
            @if($isUploading)
                <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <div class="flex items-center">
                        <flux:icon.arrow-path class="animate-spin w-5 h-5 text-blue-600 mr-2" />
                        <span class="text-sm text-blue-900 dark:text-blue-100">
                            Uploading {{ count($files) }} file(s)...
                        </span>
                    </div>
                </div>
            @elseif(!empty($completedUploads))
                <div class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                    <div class="flex items-center">
                        <flux:icon.check-circle class="w-5 h-5 text-green-600 mr-2" />
                        <span class="text-sm text-green-900 dark:text-green-100">
                            {{ count($completedUploads) }} file(s) uploaded successfully
                        </span>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>

<script>
document.addEventListener('livewire:init', () => {
    let chunkingConfig = null;
    let activeUploads = new Map();

    // Get chunking configuration
    async function getChunkingConfig() {
        if (!chunkingConfig) {
            chunkingConfig = await @this.call('getChunkingConfig');
        }
        return chunkingConfig;
    }

    // File chunking utility
    function chunkFile(file, chunkSize) {
        const chunks = [];
        let start = 0;

        while (start < file.size) {
            const end = Math.min(start + chunkSize, file.size);
            chunks.push(file.slice(start, end));
            start = end;
        }

        return chunks;
    }

    // Convert chunk to base64
    function chunkToBase64(chunk) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result.split(',')[1]);
            reader.readAsDataURL(chunk);
        });
    }

    // Process file with chunking
    async function processFileWithChunking(file) {
        const config = await getChunkingConfig();

        if (!config.enabled || file.size < config.min_file_size_for_chunking) {
            // Use regular upload for small files
            return false;
        }

        try {
            // Initialize chunked upload
            const initResult = await @this.call('initializeChunkedUpload', file.name, file.size, file.type);

            if (!initResult.success) {
                console.error('Failed to initialize chunked upload:', initResult.errors);
                return true; // Handled, even if failed
            }

            if (!initResult.should_chunk) {
                return false; // Use regular upload
            }

            const uploadId = initResult.upload_id;
            const chunkSize = initResult.chunk_size;
            const chunks = chunkFile(file, chunkSize);

            activeUploads.set(uploadId, {
                file: file,
                chunks: chunks,
                uploadedChunks: 0,
                totalChunks: chunks.length,
                cancelled: false
            });

            // Upload chunks with parallel processing
            const maxParallel = config.max_parallel_uploads;
            const uploadPromises = [];

            for (let i = 0; i < chunks.length; i += maxParallel) {
                const batch = [];

                for (let j = 0; j < maxParallel && i + j < chunks.length; j++) {
                    const chunkIndex = i + j;
                    batch.push(uploadChunk(uploadId, chunkIndex, chunks[chunkIndex]));
                }

                await Promise.all(batch);

                // Check if upload was cancelled
                const upload = activeUploads.get(uploadId);
                if (upload && upload.cancelled) {
                    break;
                }
            }

            return true; // Handled with chunking

        } catch (error) {
            console.error('Chunked upload error:', error);
            return true; // Handled, even if failed
        }
    }

    // Upload single chunk
    async function uploadChunk(uploadId, chunkIndex, chunk) {
        try {
            const upload = activeUploads.get(uploadId);
            if (upload && upload.cancelled) {
                return;
            }

            const base64Data = await chunkToBase64(chunk);
            const result = await @this.call('uploadChunk', uploadId, chunkIndex, base64Data);

            if (result.success) {
                if (upload) {
                    upload.uploadedChunks++;
                }

                if (result.completed) {
                    activeUploads.delete(uploadId);
                }
            }

        } catch (error) {
            console.error(`Error uploading chunk ${chunkIndex}:`, error);
        }
    }

    // Override file input handling for chunking
    window.handleFileSelection = async function(files) {
        const fileArray = Array.from(files);
        const regularFiles = [];

        for (const file of fileArray) {
            const wasChunked = await processFileWithChunking(file);
            if (!wasChunked) {
                regularFiles.push(file);
            }
        }

        // Handle remaining files with regular upload
        if (regularFiles.length > 0) {
            @this.uploadMultiple('files', regularFiles);
        }
    };

    // Listen for upload events
    Livewire.on('upload-started', (fileCount) => {
        console.log(`Upload started for ${fileCount} files`);
    });

    Livewire.on('file-uploaded', (fileId, index) => {
        console.log(`File ${index} uploaded with ID: ${fileId}`);
    });

    Livewire.on('file-upload-failed', (index, message) => {
        console.error(`Upload failed for file ${index}: ${message}`);
    });

    Livewire.on('upload-completed', (successCount) => {
        console.log(`Upload completed. ${successCount} files uploaded successfully`);
    });

    // Listen for chunked upload events
    Livewire.on('chunk-upload-initialized', (uploadId, fileName) => {
        console.log(`Chunked upload initialized for ${fileName} with ID: ${uploadId}`);
    });

    Livewire.on('chunk-progress', (uploadId, progress, receivedChunks, totalChunks) => {
        console.log(`Upload progress for ${uploadId}: ${progress}% (${receivedChunks}/${totalChunks} chunks)`);
    });

    Livewire.on('chunk-upload-completed', (uploadId, fileId) => {
        console.log(`Chunked upload completed for ${uploadId}, file ID: ${fileId}`);
        activeUploads.delete(uploadId);
    });

    Livewire.on('chunk-upload-failed', (uploadId, message) => {
        console.error(`Chunked upload failed for ${uploadId}: ${message}`);
        activeUploads.delete(uploadId);
    });

    Livewire.on('chunk-upload-cancelled', (uploadId) => {
        console.log(`Chunked upload cancelled for ${uploadId}`);
        activeUploads.delete(uploadId);
    });

    // Cancel upload function
    window.cancelChunkedUpload = function(uploadId) {
        const upload = activeUploads.get(uploadId);
        if (upload) {
            upload.cancelled = true;
            @this.call('cancelChunkedUpload', uploadId);
        }
    };
});
</script>
