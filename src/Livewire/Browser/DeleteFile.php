<?php

namespace Idkwhoami\FluxFiles\Livewire\Browser;

use Flux\Flux;
use Idkwhoami\FluxFiles\Livewire\FileBrowser;
use Idkwhoami\FluxFiles\Models\File;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class DeleteFile extends Component
{
    public ?int $fileId = null;
    public $file = null;

    public function mount(int $fileId): void
    {
        $this->fileId = $fileId;
        $this->file = config('flux-files.eloquent.file.model', File::class)::findOrFail($fileId);
    }

    public function deleteFile(): void
    {
        if ($this->file) {
            $this->file->delete();
            $this->dispatch('file-deleted')->to(FileBrowser::class);
            Flux::modal("delete-file-modal-$this->fileId")->close();
        }
    }

    public function render(): View
    {
        return view('flux-files::livewire.browser.delete-file');
    }
}
