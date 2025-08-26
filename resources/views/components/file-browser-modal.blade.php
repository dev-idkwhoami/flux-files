@props(['name' => 'file-browser-modal', 'title' => 'Select File', 'folderId' => null, 'allowedTypes' => [], 'onSelect' => null])

<flux:modal :name="$name" class="w-full max-w-4xl">
    <flux:modal.header>
        <flux:heading>{{ $title }}</flux:heading>
    </flux:modal.header>

    <flux:modal.body class="space-y-4">
        <livewire:flux-files.browser
            :folderId="$folderId"
            :allowedTypes="$allowedTypes"
            :onFileSelect="$onSelect"
            :key="'file-browser-modal-' . $name"
        />
    </flux:modal.body>

    <flux:modal.footer>
        <div class="flex justify-end space-x-2">
            <flux:modal.close>
                <flux:button variant="ghost">Cancel</flux:button>
            </flux:modal.close>
        </div>
    </flux:modal.footer>
</flux:modal>
