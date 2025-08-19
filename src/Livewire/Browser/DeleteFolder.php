<?php

namespace Idkwhoami\FluxFiles\Livewire\Browser;

use Flux\Flux;
use Idkwhoami\FluxFiles\Livewire\FileBrowser;
use Idkwhoami\FluxFiles\Models\Folder;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class DeleteFolder extends Component
{
    public ?int $folderId = null;
    public $folder = null;

    public function mount(int $folderId): void
    {
        $this->folderId = $folderId;
        $this->folder = config('flux-files.eloquent.folder.model', Folder::class)::findOrFail($folderId);
    }

    public function deleteFolder(): void
    {
        if ($this->folder) {
            $this->folder->delete();
            $this->dispatch('folder-deleted')->to(FileBrowser::class);
            Flux::modal("delete-folder-modal-$this->folderId")->close();
        }
    }

    public function render(): View
    {
        return view('flux-files::livewire.browser.delete-folder');
    }
}
