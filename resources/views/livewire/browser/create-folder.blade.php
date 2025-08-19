<div>
    <form class="space-y-6" wire:submit.prevent="createFolder">
        <flux:heading>Create New Folder</flux:heading>

        <flux:input wire:model="name" label="Folder Name" placeholder="Enter folder name"/>

        <div class="flex space-x-2">
            <flux:spacer/>

            <flux:modal.close>
                <flux:button variant="ghost">Cancel</flux:button>
            </flux:modal.close>
            <flux:button type="submit" variant="primary">Create</flux:button>
        </div>
    </form>
</div>
