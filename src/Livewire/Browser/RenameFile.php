<?php

namespace Idkwhoami\FluxFiles\Livewire\Browser;

use Flux\Flux;
use Idkwhoami\FluxFiles\Livewire\FileBrowser;
use Idkwhoami\FluxFiles\Models\File;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class RenameFile extends Component
{
    public string $name = '';
    public ?int $fileId = null;
    public $file = null;

    public function mount(int $fileId): void
    {
        $this->fileId = $fileId;
        $this->file = config('flux-files.eloquent.file.model', File::class)::findOrFail($fileId);

        $this->name = $this->file->name;
    }

    public function renameFile(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
        ]);

        if ($this->file) {
            $this->file->update(['name' => $this->name]);
            $this->dispatch('file-renamed')->to(FileBrowser::class);
            Flux::modal("rename-file-modal-$this->fileId")->close();
        }
    }

    public function render(): View
    {
        return view('flux-files::livewire.browser.rename-file');
    }
}
