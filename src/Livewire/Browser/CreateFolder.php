<?php

namespace Idkwhoami\FluxFiles\Livewire\Browser;

use Flux\Flux;
use Idkwhoami\FluxFiles\Livewire\FileBrowser;
use Idkwhoami\FluxFiles\Models\Folder;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CreateFolder extends Component
{
    public string $name = '';

    public ?int $folderId = null;
    public ?int $tenantId = null;

    public function mount(?int $folderId = null, ?int $tenantId = null): void
    {
        $this->folderId = $folderId;
        $this->tenantId = $tenantId;
    }

    public function createFolder(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
        ]);

        config('flux-files.eloquent.folder.model', Folder::class)::create(
            array_merge(
                [
                    'name' => $this->name,
                    'path' => $this->name,
                    'parent_id' => $this->folderId,
                ],
                config('flux-files.tenancy.enabled', false)
                    ? ['tenant_id' => $this->tenantId]
                    : []
            )
        );

        $this->dispatch('folder-created')->to(FileBrowser::class);
        Flux::modal('create-folder-modal')->close();
    }

    public function render(): View
    {
        return view('flux-files::livewire.browser.create-folder');
    }
}
