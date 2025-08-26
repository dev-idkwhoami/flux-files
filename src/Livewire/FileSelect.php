<?php

namespace Idkwhoami\FluxFiles\Livewire;

use Idkwhoami\FluxFiles\Models\File;
use Idkwhoami\FluxFiles\Traits\HasFileIcons;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class FileSelect extends Component
{
    use HasFileIcons;
    public ?int $selectedFileId = null;
    public ?File $selectedFile = null;
    public string $placeholder = 'Select a file...';
    public bool $required = false;
    public array $allowedFileTypes = [];
    public ?int $restrictToFolder = null;
    public ?int $tenantId = null;

    protected $listeners = [
        'file-selected' => 'handleFileSelected',
        'modal-closed' => 'closeModal'
    ];

    public function mount(
        ?int $selectedFileId = null,
        string $placeholder = 'Select a file...',
        bool $required = false,
        array $allowedFileTypes = [],
        ?int $restrictToFolder = null,
        ?int $tenantId = null
    ): void {
        $this->selectedFileId = $selectedFileId;
        $this->placeholder = $placeholder;
        $this->required = $required;
        $this->allowedFileTypes = $allowedFileTypes;
        $this->restrictToFolder = $restrictToFolder;
        $this->tenantId = $tenantId;

        if ($selectedFileId) {
            $this->selectedFile = config('flux-files.eloquent.file.model', File::class)::find($selectedFileId);
        }
    }

    public function handleFileSelected($fileData): void
    {
        $this->selectedFileId = $fileData['id'];
        $this->selectedFile = config('flux-files.eloquent.file.model', File::class)::find($fileData['id']);

        // Dispatch event for parent components
        $this->dispatch('file-selection-changed', [
            'fileId' => $this->selectedFileId,
            'file' => $this->selectedFile?->toArray()
        ]);
    }

    public function selectFile(int $fileId): void
    {
        $file = config('flux-files.eloquent.file.model', File::class)::find($fileId);
        if ($file) {
            $this->handleFileSelected($file->toArray());
        }
    }

    public function clearSelection(): void
    {
        $this->selectedFileId = null;
        $this->selectedFile = null;

        $this->dispatch('file-selection-changed', [
            'fileId' => null,
            'file' => null
        ]);
    }

    #[Computed]
    public function hasSelection(): bool
    {
        return $this->selectedFile !== null;
    }

    public function render(): View
    {
        return view('flux-files::livewire.file-select');
    }
}
