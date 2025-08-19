<div class="space-y-6">
    <flux:heading>Delete {{ $file?->name ?? 'File' }}</flux:heading>

    <flux:text>This action is irreversible. Are you sure you want to delete anyway?</flux:text>

    <div class="flex space-x-2">
        <flux:spacer/>

        <flux:modal.close>
            <flux:button variant="ghost">Cancel</flux:button>
        </flux:modal.close>
        <flux:button wire:click="deleteFile" variant="danger">Delete</flux:button>
    </div>
</div>
