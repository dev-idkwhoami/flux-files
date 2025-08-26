<div class="flux-files-select">
    {{-- File selection input display --}}
    <flux:button.group>
        <flux:modal.trigger name="file-select-modal">
            <flux:button
                variant="outline"
                class="w-full justify-between text-left"
                :class="!$this->hasSelection ? 'text-neutral-500' : ''"
            >
                <div class="flex items-center space-x-2">
                    @if($this->hasSelection && $selectedFile)
                        <flux:icon :name="$this->getFileIcon($selectedFile)" class="size-4"/>
                        <span>{{ $selectedFile->original_name }}</span>
                    @else
                        <flux:icon name="document" class="size-4"/>
                        <span>{{ $placeholder }}</span>
                    @endif
                </div>
            </flux:button>
        </flux:modal.trigger>
        {{-- Clear selection button --}}
        @if($this->hasSelection && !$required)
            <flux:button
                wire:click="clearSelection"
                square
                icon="x-mark"
                tooltip="Clear selection"
            />
        @endif
    </flux:button.group>

    {{-- File browser modal --}}
    <flux:modal name="file-select-modal" class="w-2/3 md:max-w-4xl">
        <flux:heading size="lg">Select a File</flux:heading>

        <livewire:flux-files.file-browser
            :allowedFileTypes="$allowedFileTypes"
            :selectedFile="$selectedFileId"
            :restrictToFolder="$restrictToFolder"
            :tenantId="$tenantId"
            :showActions="false"
            :allowFolderCreation="false"
            wire:key="file-select-browser"
        />

        <div class="flex space-x-2">
            <flux:spacer/>
            <flux:modal.close>
                <flux:button
                    variant="primary"
                >
                    Close
                </flux:button>
            </flux:modal.close>
        </div>
    </flux:modal>
</div>
