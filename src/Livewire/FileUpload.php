<?php

namespace Idkwhoami\FluxFiles\Livewire;

use Idkwhoami\FluxFiles\Services\FileValidationService;
use Idkwhoami\FluxFiles\Services\FileStorageService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Modelable;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class FileUpload extends Component
{
    use WithFileUploads;

    protected $listeners = [
        //'upload-completed' => 'handleFileUploadCompleted'
    ];

    // Component properties
    public ?int $targetFolderId = null;
    public array $allowedTypes = [];
    public ?int $maxFileSize = null;
    public ?int $maxFiles = null;
    public bool $multiple = true;
    public bool $showPreviews = true;
    public bool $dragDrop = true;

    // Upload state
    #[Modelable]
    public array $files = [];

    public array $validationErrors = [];
    public array $completedUploads = [];
    public bool $isUploading = false;


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

    public function handleFileUploadCompleted(): void
    {
        if ($this->targetFolderId === null) {
            return;
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

        $this->dispatch('upload-started', fileCount: count($this->files));

        foreach ($this->files as $index => $file) {
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

            $this->dispatch('file-uploaded', fileId: $storedFile->id, index: $index);

        } catch (\Throwable $e) {
            $this->validationErrors[$index] = ['Upload failed: '.$e->getMessage()];
            $this->dispatch('file-upload-failed', index: $index, message: $e->getMessage());
        }

        // Check if all files are processed
        if (count($this->completedUploads) + count($this->validationErrors) === count($this->files)) {
            $this->isUploading = false;
            $this->dispatch('upload-completed', successCount: count($this->completedUploads));
        }
    }

    public function removeFile(int $index): void
    {
        unset($this->files[$index]);
        unset($this->validationErrors[$index]);
        unset($this->completedUploads[$index]);

        $this->files = array_values($this->files);
    }

    public function clearAll(): void
    {
        $this->files = [];
        $this->validationErrors = [];
        $this->completedUploads = [];
        $this->isUploading = false;
    }

    /**
     * Get preview URL for a file (TemporaryUploadedFile or chunked display object)
     */
    public function getPreviewUrl(mixed $file): ?string
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

    /**
     * Get appropriate icon for a file (TemporaryUploadedFile or chunked display object)
     */
    public function getFileIcon(mixed $file): string
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


    public function render(): View
    {
        return view('flux-files::livewire.file-upload');
    }
}
