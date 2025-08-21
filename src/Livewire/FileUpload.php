<?php

namespace Idkwhoami\FluxFiles\Livewire;

use Idkwhoami\FluxFiles\Models\File;
use Idkwhoami\FluxFiles\Models\Folder;
use Idkwhoami\FluxFiles\Services\FileValidationService;
use Idkwhoami\FluxFiles\Services\FileStorageService;
use Idkwhoami\FluxFiles\Services\ChunkedUploadService;
use Illuminate\Http\UploadedFile;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class FileUpload extends Component
{
    use WithFileUploads;

    // Component properties
    public ?int $targetFolderId = null;
    public array $uploadedFiles = [];
    public array $allowedTypes = [];
    public ?int $maxFileSize = null;
    public ?int $maxFiles = null;
    public bool $multiple = true;
    public bool $showPreviews = true;
    public bool $dragDrop = true;

    // Upload state
    public array $files = [];
    public array $uploadProgress = [];
    public array $validationErrors = [];
    public array $completedUploads = [];
    public bool $isUploading = false;

    // Chunking configuration
    public int $chunkSize;
    public int $maxParallelUploads;
    public int $minFileSizeForChunking;
    public bool $chunkingEnabled;

    public function mount(
        ?int $targetFolderId = null,
        array $allowedTypes = [],
        ?int $maxFileSize = null,
        ?int $maxFiles = null,
        bool $multiple = true,
        bool $showPreviews = true,
        bool $dragDrop = true
    ): void {
        $this->targetFolderId = $targetFolderId;
        $this->allowedTypes = $allowedTypes;
        $this->maxFileSize = $maxFileSize ?? config('flux-files.validation.max_file_size');
        $this->maxFiles = $maxFiles ?? config('flux-files.validation.max_files_per_upload');
        $this->multiple = $multiple;
        $this->showPreviews = $showPreviews;
        $this->dragDrop = $dragDrop;

        // Initialize chunking configuration
        $this->chunkSize = config('flux-files.upload.chunk_size', 1048576);
        $this->maxParallelUploads = config('flux-files.upload.max_parallel_uploads', 3);
        $this->minFileSizeForChunking = config('flux-files.upload.min_file_size_for_chunking', 5242880); // 5MB
        $this->chunkingEnabled = config('flux-files.upload.chunking_enabled', true);
    }

    #[Computed]
    public function validationService(): FileValidationService
    {
        return app(FileValidationService::class);
    }

    #[Computed]
    public function storageService(): FileStorageService
    {
        return app(FileStorageService::class);
    }

    #[Computed]
    public function chunkedUploadService(): ChunkedUploadService
    {
        return app(ChunkedUploadService::class);
    }

    public function updatedFiles(): void
    {
        if (empty($this->files)) {
            return;
        }

        $this->validateFiles();

        if (empty($this->validationErrors)) {
            $this->startUpload();
        }
    }

    protected function validateFiles(): void
    {
        $this->validationErrors = [];

        // Check max files limit
        if ($this->maxFiles && count($this->files) > $this->maxFiles) {
            $this->validationErrors['max_files'] = "Maximum {$this->maxFiles} files allowed";
            return;
        }

        foreach ($this->files as $index => $file) {
            if (!$file instanceof TemporaryUploadedFile) {
                continue;
            }

            $errors = $this->validationService->validateFile(
                $file,
                $this->targetFolderId,
                null, // tenantId - will be handled later
                [
                    'allowed_extensions' => $this->allowedTypes,
                    'max_file_size' => $this->maxFileSize
                ]
            );

            if (!empty($errors)) {
                $this->validationErrors[$index] = $errors;
            }
        }
    }

    protected function startUpload(): void
    {
        $this->isUploading = true;
        $this->uploadProgress = [];

        $this->dispatch('upload-started', count($this->files));

        foreach ($this->files as $index => $file) {
            $this->uploadProgress[$index] = 0;
            $this->processFileUpload($file, $index);
        }
    }

    protected function processFileUpload(TemporaryUploadedFile $file, int $index): void
    {
        try {
            // Store the file using the storage service
            $storedFile = $this->storageService->store(
                $file,
                $this->targetFolderId,
                config('flux-files.storage.disk'),
                null // tenantId - will be handled later
            );

            $this->completedUploads[$index] = [
                'file' => $storedFile,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType()
            ];

            $this->uploadProgress[$index] = 100;
            $this->dispatch('file-uploaded', $storedFile->id, $index);

        } catch (\Exception $e) {
            $this->validationErrors[$index] = ['Upload failed: '.$e->getMessage()];
            $this->dispatch('file-upload-failed', $index, $e->getMessage());
        }

        // Check if all files are processed
        if (count($this->completedUploads) + count($this->validationErrors) === count($this->files)) {
            $this->isUploading = false;
            $this->dispatch('upload-completed', count($this->completedUploads));
        }
    }

    public function removeFile(int $index): void
    {
        unset($this->files[$index]);
        unset($this->uploadProgress[$index]);
        unset($this->validationErrors[$index]);
        unset($this->completedUploads[$index]);

        $this->files = array_values($this->files);
    }

    public function clearAll(): void
    {
        $this->files = [];
        $this->uploadProgress = [];
        $this->validationErrors = [];
        $this->completedUploads = [];
        $this->isUploading = false;
    }

    public function getPreviewUrl(TemporaryUploadedFile $file): ?string
    {
        if (!$this->showPreviews) {
            return null;
        }

        $mimeType = $file->getMimeType();

        if (str_starts_with($mimeType, 'image/')) {
            return $file->temporaryUrl();
        }

        return null;
    }

    public function isPreviewable(TemporaryUploadedFile $file): bool
    {
        $mimeType = $file->getMimeType();
        return str_starts_with($mimeType, 'image/') ||
            str_starts_with($mimeType, 'video/') ||
            str_starts_with($mimeType, 'audio/');
    }

    public function getFileIcon(TemporaryUploadedFile $file): string
    {
        $mimeType = $file->getMimeType();
        $icons = config('flux-files.ui.file_icons');

        if (str_starts_with($mimeType, 'image/')) {
            return $icons['image'];
        } elseif (str_starts_with($mimeType, 'video/')) {
            return $icons['video'];
        } elseif (str_starts_with($mimeType, 'audio/')) {
            return $icons['audio'];
        } elseif (in_array($mimeType, [
            'application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ])) {
            return $icons['document'];
        } elseif (in_array(
            $mimeType,
            ['application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed']
        )) {
            return $icons['archive'];
        }

        return $icons['default'];
    }

    // Chunked upload methods
    public function initializeChunkedUpload(string $fileName, int $fileSize, string $mimeType): array
    {
        try {
            // Create a temporary UploadedFile for validation
            $tempFile = new class ($fileName, $mimeType, $fileSize) extends UploadedFile {
                private $clientOriginalName;
                private $mimeType;
                private $size;

                public function __construct(string $name, string $mimeType, int $size)
                {
                    $this->clientOriginalName = $name;
                    $this->mimeType = $mimeType;
                    $this->size = $size;
                }

                public function getClientOriginalName(): string
                {
                    return $this->clientOriginalName;
                }

                public function getMimeType(): string
                {
                    return $this->mimeType;
                }

                public function getSize(): int
                {
                    return $this->size;
                }

                public function getClientOriginalExtension(): string
                {
                    return pathinfo($this->clientOriginalName, PATHINFO_EXTENSION);
                }

                public function getClientMimeType(): string
                {
                    return $this->mimeType;
                }

                public function getPathname(): string
                {
                    return '';
                }

                public function isValid(): bool
                {
                    return true;
                }

                public function getError(): int
                {
                    return UPLOAD_ERR_OK;
                }
            };

            // Validate the file
            $errors = $this->validationService->validateFile(
                $tempFile,
                $this->targetFolderId,
                null,
                [
                    'allowed_extensions' => $this->allowedTypes,
                    'max_file_size' => $this->maxFileSize
                ]
            );

            if (!empty($errors)) {
                return [
                    'success' => false,
                    'errors' => $errors
                ];
            }

            // Initialize chunked upload
            $uploadId = $this->chunkedUploadService->initializeChunkedUpload($tempFile);

            $this->dispatch('chunk-upload-initialized', $uploadId, $fileName);

            return [
                'success' => true,
                'upload_id' => $uploadId,
                'chunk_size' => $this->chunkedUploadService->getChunkSize(),
                'should_chunk' => $this->chunkedUploadService->shouldUseChunking($tempFile)
            ];

        } catch (\Exception $e) {
            $this->dispatch('chunk-upload-failed', $fileName, $e->getMessage());

            return [
                'success' => false,
                'errors' => ['Upload initialization failed: '.$e->getMessage()]
            ];
        }
    }

    public function uploadChunk(string $uploadId, int $chunkIndex, string $chunkData): array
    {
        try {
            $result = $this->chunkedUploadService->uploadChunk($uploadId, $chunkIndex, $chunkData);

            // Update progress
            $progress = $this->chunkedUploadService->getUploadProgress($uploadId);
            $this->dispatch(
                'chunk-progress',
                $uploadId,
                $progress['progress'],
                $progress['received_chunks'],
                $progress['total_chunks']
            );

            if ($result['completed']) {
                // File upload completed
                $file = File::find($result['file_id']);

                // Update file with correct folder
                if ($this->targetFolderId) {
                    $file->folder_id = $this->targetFolderId;
                    $file->save();
                }

                $this->dispatch('chunk-upload-completed', $uploadId, $file->id);

                return [
                    'success' => true,
                    'completed' => true,
                    'file_id' => $file->id,
                    'progress' => 100
                ];
            }

            return [
                'success' => true,
                'completed' => false,
                'progress' => $result['progress'],
                'received_chunks' => $result['received_chunks'],
                'total_chunks' => $result['total_chunks']
            ];

        } catch (\Exception $e) {
            $this->dispatch('chunk-upload-failed', $uploadId, $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function cancelChunkedUpload(string $uploadId): void
    {
        try {
            $this->chunkedUploadService->cancelUpload($uploadId);
            $this->dispatch('chunk-upload-cancelled', $uploadId);
        } catch (\Exception $e) {
            $this->dispatch('chunk-upload-failed', $uploadId, $e->getMessage());
        }
    }

    public function getChunkingConfig(): array
    {
        return [
            'enabled' => $this->chunkingEnabled,
            'chunk_size' => $this->chunkSize,
            'max_parallel_uploads' => $this->maxParallelUploads,
            'min_file_size_for_chunking' => $this->minFileSizeForChunking
        ];
    }

    public function render()
    {
        return view('flux-files::livewire.file-upload');
    }
}
