@props(['file'])

<flux:dropdown>
    <flux:button square size="xs" variant="ghost" icon="ellipsis-vertical"/>
    <flux:menu>
        <flux:modal.trigger name="rename-file-modal-{{$file->id}}">
            <flux:menu.item>
                <flux:icon name="pencil" class="size-4 mr-2"/>
                Rename
            </flux:menu.item>
        </flux:modal.trigger>
        <flux:modal.trigger name="delete-file-modal-{{$file->id}}">
            <flux:menu.item>
                <flux:icon name="trash-2" class="size-4 mr-2"/>
                Delete
            </flux:menu.item>
        </flux:modal.trigger>
        <flux:modal.trigger name="move-file-modal-{{$file->id}}">
            <flux:menu.item disabled>
                <flux:icon name="move" class="size-4 mr-2"/>
                Move (Not implemented)
            </flux:menu.item>
        </flux:modal.trigger>
    </flux:menu>
</flux:dropdown>

{{-- Modals for file actions --}}
<flux:modal name="rename-file-modal-{{$file->id}}">
    <livewire:flux-files.browser.rename-file :fileId="$file->id" :key="'rename-file-'.$file->id"/>
</flux:modal>

<flux:modal name="delete-file-modal-{{$file->id}}">
    <livewire:flux-files.browser.delete-file :fileId="$file->id" :key="'delete-file-'.$file->id"/>
</flux:modal>
