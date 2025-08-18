<?php

namespace Idkwhoami\FluxFiles\Livewire;

use Idkwhoami\FluxFiles\DataObjects\Breadcrumb;
use Idkwhoami\FluxFiles\Models\File;
use Idkwhoami\FluxFiles\Models\Folder;
use Idkwhoami\FluxFiles\Enums\FileExtension;
use Idkwhoami\FluxFiles\Enums\MimeType;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class FileBrowser extends Component
{
    use WithPagination;

    public ?int $currentFolderId = null;

    public string $viewMode = 'grid';
    public array $allowedFileTypes = [];

    public ?int $openToDirectory = null;
    public ?int $tenantId = null;

    public string $sortBy = 'name';
    public string $sortDirection = 'desc';

    protected $listeners = [
        'folderChanged' => 'handleFolderChanged'
    ];

    public function mount(
        ?int $folderId = null,
        ?string $viewMode = null,
        array $allowedFileTypes = [],
        ?int $openToDirectory = null,
        ?int $tenantId = null
    ): void {
        $this->viewMode = $viewMode ?? config('flux-files.ui.default_view_mode', 'grid');
        $this->allowedFileTypes = !empty($allowedFileTypes) ? $allowedFileTypes : FileExtension::allExtensions();
        $this->tenantId = $tenantId;

        $this->currentFolderId = $openToDirectory ?? $folderId;
    }

    public function navigateToFolder(?int $folderId = null): void
    {
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
        $file = File::find($fileId);
        if ($file) {
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
        $query = Folder::query()
            ->when(!is_null($this->tenantId), fn ($query) => $query->byTenant($this->tenantId));

        if ($this->currentFolderId) {
            $query->where('parent_id', $this->currentFolderId);
        } else {
            $query->roots();
        }

        return $query->orderBy($this->sortBy, $this->sortDirection)->get();
    }

    #[Computed]
    public function files(): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = File::query();

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
        return $this->currentFolderId ? Folder::find($this->currentFolderId) : null;
    }

    #[Computed]
    public function breadcrumbs(): array
    {
        /* TODO for some reason breadcrumbs dont include the current folder */
        $breadcrumbs = [];

        if (!$this->currentFolderId) {
            $breadcrumbs[] = Breadcrumb::root();
            return $breadcrumbs;
        }

        $folder = Folder::find($this->currentFolderId);
        $breadcrumbItems = [];

        while ($folder) {
            array_unshift($breadcrumbItems, Breadcrumb::folder(
                id: $folder->id,
                name: $folder->name,
                path: $folder->path
            ));
            $folder = $folder->parent;
        }

        array_unshift($breadcrumbItems, Breadcrumb::root());

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

    public function getFileIcon(File $file): string
    {
        $icons = config('flux-files.ui.file_icons');

        if ($file->isImage()) {
            return $icons['image'] ?? 'file-question-mark';
        }

        if ($file->isVideo()) {
            return $icons['video'] ?? 'file-question-mark';
        }

        if ($file->isAudio()) {
            return $icons['audio'] ?? 'file-question-mark';
        }

        // Check for archive files by extension
        $extension = strtolower(pathinfo($file->original_name, PATHINFO_EXTENSION));
        if (in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz'])) {
            return $icons['archive'] ?? 'file-question-mark';
        }

        // Check for document files
        if (str_starts_with($file->mime_type, 'application/') || str_starts_with($file->mime_type, 'text/')) {
            return $icons['document'] ?? 'file-question-mark';
        }

        return $icons['default'] ?? 'file-question-mark';
    }

    protected function handleFolderChanged($folderId): void
    {
        $this->navigateToFolder($folderId);
    }

    public function render(): View
    {
        return view('flux-files::livewire.file-browser');
    }
}
