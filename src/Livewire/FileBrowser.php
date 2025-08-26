<?php

namespace Idkwhoami\FluxFiles\Livewire;

use Idkwhoami\FluxFiles\Concrete\Breadcrumb;
use Idkwhoami\FluxFiles\Enums\FileExtension;
use Idkwhoami\FluxFiles\Enums\MimeType;
use Idkwhoami\FluxFiles\Models\File;
use Idkwhoami\FluxFiles\Models\Folder;
use Idkwhoami\FluxFiles\Traits\HasFileIcons;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Session;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Idkwhoami\FluxFiles\Services\FileStorageService;

class FileBrowser extends Component
{
    use WithPagination;
    use WithFileUploads;
    use HasFileIcons;

    public ?int $currentFolderId = null;

    #[Session('flux-files::view_mode')]
    public string $viewMode = 'grid';
    public array $allowedFileTypes = [];

    public ?int $openToDirectory = null;
    public ?int $tenantId = null;
    public ?int $selected_file_id = null;
    public ?int $restrictToFolder = null;
    public bool $showActions = true;
    public bool $allowFolderCreation = true;

    public string $sortBy = 'name';
    public string $sortDirection = 'desc';

    // File upload properties
    public $tempFile;
    public bool $isUploading = false;

    protected $listeners = [
        'folderChanged' => 'handleFolderChanged',
        'folder-created' => '$refresh',
        'folder-renamed' => '$refresh',
        'folder-deleted' => '$refresh',
        'file-renamed' => '$refresh',
        'file-deleted' => '$refresh',
        'upload-completed' => '$refresh',
        'start-upload' => 'startUpload',
        'end-upload' => 'endUpload'
    ];

    public function mount(
        ?string $viewMode = null,
        array $allowedFileTypes = [],
        ?int $openToDirectory = null,
        ?int $tenantId = null,
        ?int $selectedFile = null,
        ?int $restrictToFolder = null,
        bool $showActions = true,
        bool $allowFolderCreation = true
    ): void {
        $this->viewMode = $viewMode ?? \session()->get('flux-files::view_mode') ?? config(
            'flux-files.ui.default_view_mode',
            'grid'
        );
        $this->allowedFileTypes = !empty($allowedFileTypes) ? $allowedFileTypes : FileExtension::allExtensions();
        $this->tenantId = $tenantId;
        $this->selected_file_id = $selectedFile;
        $this->restrictToFolder = $restrictToFolder;
        $this->showActions = $showActions;
        $this->allowFolderCreation = $allowFolderCreation;

        // If a selectedFileId is provided, automatically navigate to its folder
        if ($selectedFile) {
            $selectedFile = config('flux-files.eloquent.file.model', File::class)::find($selectedFile);
            if ($selectedFile) {
                $this->currentFolderId = $selectedFile->folder_id;
            } else {
                $this->currentFolderId = $openToDirectory;
            }
        } else {
            $this->currentFolderId = $openToDirectory;
        }
    }

    public function navigateToFolder(?int $folderId = null): void
    {
        // Check if navigation is restricted and if we're trying to go outside the boundary
        if ($this->restrictToFolder !== null && $folderId !== null) {
            if (!$this->isWithinRestrictedBoundary($folderId)) {
                return; // Don't allow navigation outside the restricted folder
            }
        } elseif ($this->restrictToFolder !== null && $folderId === null) {
            // Don't allow navigation to root if we have a restricted folder
            return;
        }

        $this->currentFolderId = $folderId;
        $this->resetPage();
        $this->dispatch('folder-changed', $folderId);
    }

    public function toggleViewMode(): void
    {
        $this->viewMode = $this->viewMode === 'grid' ? 'table' : 'grid';
    }

    public function selectFile(int $fileId): void
    {
        $file = config('flux-files.eloquent.file.model', File::class)::find($fileId);
        if ($file) {
            $this->selected_file_id = $fileId;
            $this->dispatch('file-selected', $file->toArray());
        }
    }

    public function updatedSortBy(): void
    {
        $this->resetPage();
        $this->sortDirection = 'desc';
    }

    public function toggleSortDirection(): void
    {
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
    }

    #[Computed]
    public function folders(): \Illuminate\Database\Eloquent\Collection
    {
        $query = config('flux-files.eloquent.folder.model', Folder::class)::query()
            ->when(!is_null($this->tenantId), fn ($query) => $query->byTenant($this->tenantId));

        if ($this->currentFolderId) {
            $query->where('parent_id', $this->currentFolderId);
        } else {
            $query->roots();
        }

        // Folders don't have size or mime_type fields, so sort by name when these are selected
        $sortField = ($this->sortBy === 'size' || $this->sortBy === 'mime_type') ? 'name' : $this->sortBy;
        return $query->orderBy($sortField, $this->sortDirection)->get();
    }

    #[Computed]
    public function files(): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = config('flux-files.eloquent.file.model', File::class)::query();

        if ($this->tenantId) {
            $query->byTenant($this->tenantId);
        }

        if ($this->currentFolderId) {
            $query->inFolder($this->currentFolderId);
        } else {
            $query->whereNull('folder_id');
        }

        if (!empty($this->allowedFileTypes)) {
            $allowedMimeTypes = $this->getAllowedMimeTypes();
            if (!empty($allowedMimeTypes)) {
                $query->whereIn('mime_type', $allowedMimeTypes);
            }
        }

        return $query->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(config('flux-files.ui.items_per_page', 20));
    }

    #[Computed]
    public function currentFolder(): ?Folder
    {
        return $this->currentFolderId ? config('flux-files.eloquent.folder.model', Folder::class)::find($this->currentFolderId) : null;
    }

    #[Computed]
    public function breadcrumbs(): array
    {
        $breadcrumbs = [];

        if (!$this->currentFolderId) {
            // If we have a restricted folder, show that as root instead of true root
            if ($this->restrictToFolder) {
                $restrictedFolder = config('flux-files.eloquent.folder.model', Folder::class)::find($this->restrictToFolder);
                if ($restrictedFolder) {
                    $breadcrumbs[] = Breadcrumb::folder(
                        id: $restrictedFolder->id,
                        name: $restrictedFolder->name,
                        path: $restrictedFolder->path
                    );
                }
            } else {
                $breadcrumbs[] = Breadcrumb::root();
            }
            return $breadcrumbs;
        }

        $folder = config('flux-files.eloquent.folder.model', Folder::class)::find($this->currentFolderId);
        $breadcrumbItems = [];

        while ($folder) {
            array_unshift($breadcrumbItems, Breadcrumb::folder(
                id: $folder->id,
                name: $folder->name,
                path: $folder->path
            ));

            // Stop at the restricted folder boundary
            if ($this->restrictToFolder && $folder->id === $this->restrictToFolder) {
                break;
            }

            $folder = $folder->parent;
        }

        // Add root or restricted folder as the first item
        if ($this->restrictToFolder) {
            $restrictedFolder = config('flux-files.eloquent.folder.model', Folder::class)::find($this->restrictToFolder);
            if ($restrictedFolder && (!$breadcrumbItems || $breadcrumbItems[0]->id !== $this->restrictToFolder)) {
                array_unshift($breadcrumbItems, Breadcrumb::folder(
                    id: $restrictedFolder->id,
                    name: $restrictedFolder->name,
                    path: $restrictedFolder->path
                ));
            }
        } else {
            array_unshift($breadcrumbItems, Breadcrumb::root());
        }

        $maxItems = config('flux-files.ui.breadcrumbs_max_items', 5);
        if (count($breadcrumbItems) > $maxItems) {
            return array_merge(
                array_slice($breadcrumbItems, 0, 2),
                [Breadcrumb::ellipsis()],
                array_slice($breadcrumbItems, -($maxItems - 3))
            );
        } else {
            return $breadcrumbItems;
        }
    }

    protected function getAllowedMimeTypes(): array
    {
        $extensionToMimeMapping = [
            'jpg' => MimeType::IMAGE_JPEG->value,
            'jpeg' => MimeType::IMAGE_JPEG->value,
            'png' => MimeType::IMAGE_PNG->value,
            'gif' => MimeType::IMAGE_GIF->value,
            'webp' => MimeType::IMAGE_WEBP->value,
            'svg' => MimeType::IMAGE_SVG->value,
            'pdf' => MimeType::APPLICATION_PDF->value,
            'doc' => MimeType::APPLICATION_MSWORD->value,
            'docx' => MimeType::APPLICATION_DOCX->value,
            'xls' => MimeType::APPLICATION_EXCEL->value,
            'xlsx' => MimeType::APPLICATION_XLSX->value,
            'ppt' => MimeType::APPLICATION_POWERPOINT->value,
            'pptx' => MimeType::APPLICATION_PPTX->value,
            'txt' => MimeType::TEXT_PLAIN->value,
            'rtf' => MimeType::TEXT_RTF->value,
            'csv' => MimeType::TEXT_CSV->value,
            'mp3' => MimeType::AUDIO_MPEG->value,
            'wav' => MimeType::AUDIO_WAV->value,
            'ogg' => MimeType::AUDIO_OGG->value,
            'flac' => MimeType::AUDIO_FLAC->value,
            'mp4' => MimeType::VIDEO_MP4->value,
            'avi' => MimeType::VIDEO_AVI->value,
            'mov' => MimeType::VIDEO_QUICKTIME->value,
            'wmv' => MimeType::VIDEO_WMV->value,
            'flv' => MimeType::VIDEO_FLV->value,
            'zip' => MimeType::APPLICATION_ZIP->value,
            'rar' => MimeType::APPLICATION_RAR->value,
            '7z' => MimeType::APPLICATION_7Z->value,
            'tar' => MimeType::APPLICATION_TAR->value,
            'gz' => MimeType::APPLICATION_GZIP->value,
        ];

        $mimeTypes = [];
        foreach ($this->allowedFileTypes as $extension) {
            $extension = strtolower($extension);
            if (isset($extensionToMimeMapping[$extension])) {
                $mimeTypes[] = $extensionToMimeMapping[$extension];
            }
        }

        return array_unique($mimeTypes);
    }



    protected function isWithinRestrictedBoundary(int $folderId): bool
    {
        if ($this->restrictToFolder === null) {
            return true; // No restriction
        }

        // If trying to navigate to the restricted folder itself, that's allowed
        if ($folderId === $this->restrictToFolder) {
            return true;
        }

        // Check if the target folder is a descendant of the restricted folder
        $folder = config('flux-files.eloquent.folder.model', Folder::class)::find($folderId);
        if (!$folder) {
            return false;
        }

        // Walk up the parent chain to see if we find the restricted folder
        while ($folder && $folder->parent_id) {
            if ($folder->parent_id === $this->restrictToFolder) {
                return true;
            }
            $folder = $folder->parent;
        }

        return false;
    }

    protected function handleFolderChanged($folderId): void
    {
        $this->navigateToFolder($folderId);
    }

    public function startUpload(): void
    {
        $this->isUploading = true;
    }

    public function endUpload(): void
    {
        $this->isUploading = false;
    }

    public function processUploadedFile(string $filename): void
    {
        if (!$this->tempFile) {
            return;
        }

        try {
            $storageService = app(FileStorageService::class);

            // Store the file in the current folder
            $storedFile = $storageService->store(
                $this->tempFile,
                $this->currentFolderId,
                config('flux-files.storage.disk'),
                $this->tenantId
            );

            // Clear the temporary file
            $this->tempFile = null;

            // End upload state
            $this->endUpload();

            // Refresh the component to show the new file
            $this->dispatch('upload-completed');

        } catch (\Throwable $e) {
            // End upload state on error
            $this->endUpload();

            // Handle upload error
            $this->dispatch('upload-failed', message: $e->getMessage());
        }
    }

    public function render(): View
    {
        return view('flux-files::livewire.file-browser');
    }
}
