@props(['folder'])

<flux:dropdown>
    <flux:button square size="xs" variant="ghost" icon="ellipsis-vertical"/>
    <flux:menu>
        <flux:modal.trigger name="rename-folder-modal-{{$folder->id}}">
            <flux:menu.item>
                <flux:icon name="pencil" class="size-4 mr-2"/>
                Rename
            </flux:menu.item>
        </flux:modal.trigger>
        <flux:modal.trigger name="delete-folder-modal-{{$folder->id}}">
            <flux:menu.item>
                <flux:icon name="trash-2" class="size-4 mr-2"/>
                Delete
            </flux:menu.item>
        </flux:modal.trigger>
        <flux:modal.trigger name="move-folder-modal-{{$folder->id}}">
            <flux:menu.item disabled>
                <flux:icon name="move" class="size-4 mr-2"/>
                Move (Not implemented)
            </flux:menu.item>
        </flux:modal.trigger>
    </flux:menu>
</flux:dropdown>

{{-- Modals for folder actions --}}
<flux:modal name="rename-folder-modal-{{$folder->id}}">
    <livewire:flux-files.browser.rename-folder :folderId="$folder->id" :key="'rename-folder-'.$folder->id"/>
</flux:modal>

<flux:modal name="delete-folder-modal-{{$folder->id}}">
    <livewire:flux-files.browser.delete-folder :folderId="$folder->id" :key="'delete-folder-'.$folder->id"/>
</flux:modal>
