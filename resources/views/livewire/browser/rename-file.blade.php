<div>
    <form class="space-y-6" wire:submit.prevent="renameFile">
        <flux:heading>Rename {{ $file?->name ?? 'File' }}</flux:heading>

        <flux:input wire:model="name" label="File Name" placeholder="Enter new file name"/>

        <div class="flex space-x-2">
            <flux:spacer/>

            <flux:modal.close>
                <flux:button variant="ghost">Cancel</flux:button>
            </flux:modal.close>
            <flux:button type="submit" variant="primary">Save changes</flux:button>
        </div>
    </form>
</div>
