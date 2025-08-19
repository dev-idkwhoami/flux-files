<div>
    <form class="space-y-6" wire:submit.prevent="renameFolder">
        <flux:heading>Rename {{ $folder?->name ?? 'Folder' }}</flux:heading>

        <flux:input wire:model="name" label="Enter new folder name" placeholder="Enter new folder name"/>

        <div class="flex space-x-2">
            <flux:spacer/>

            <flux:modal.close>
                <flux:button variant="ghost">Cancel</flux:button>
            </flux:modal.close>
            <flux:button type="submit" variant="primary">Save changes</flux:button>
        </div>
    </form>
</div>
