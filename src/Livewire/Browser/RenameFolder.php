<?php

namespace Idkwhoami\FluxFiles\Livewire\Browser;

use Flux\Flux;
use Idkwhoami\FluxFiles\Livewire\FileBrowser;
use Idkwhoami\FluxFiles\Models\Folder;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class RenameFolder extends Component
{
    public string $name = '';
    public ?int $folderId = null;
    public $folder = null;

    public function mount(int $folderId): void
    {
        $this->folderId = $folderId;
        $this->folder = config('flux-files.eloquent.folder.model', Folder::class)::findOrFail($folderId);

        $this->name = $this->folder->name;
    }

    public function renameFolder(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
        ]);

        if ($this->folder) {
            $this->folder->update(['name' => $this->name]);
            $this->dispatch('folder-renamed')->to(FileBrowser::class);
            Flux::modal("rename-folder-modal-$this->folderId")->close();
        }
    }

    public function render(): View
    {
        return view('flux-files::livewire.browser.rename-folder');
    }
}
