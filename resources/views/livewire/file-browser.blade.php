<div class="flux-files-browser">
    @php
        $locale = app()->getLocale();
    @endphp
    {{-- Header with breadcrumbs and controls --}}
    <div class="flex items-center justify-between mb-4 p-3 bg-neutral-50 dark:bg-neutral-800 rounded-lg">
        {{-- Breadcrumbs --}}
        <nav class="flex items-center text-sm">
            @foreach($this->breadcrumbs as $breadcrumb)
                @if($loop->last)
                    @if($breadcrumb->isRoot())
                        <flux:button
                            disabled
                            variant="ghost"
                            size="sm"
                        >
                            <flux:icon name="house" class="size-5"/>
                        </flux:button>
                    @else
                        <flux:button
                            disabled
                            variant="ghost"
                            size="sm"
                        >
                            {{ $breadcrumb->name }}
                        </flux:button>
                    @endif
                @elseif($breadcrumb->isEllipsis)
                    <flux:text>{{ $breadcrumb->name }}</flux:text>
                @else
                    <flux:button
                        class="inline-flex"
                        wire:click="navigateToFolder({{ $breadcrumb->id }})"
                        variant="ghost"
                        size="sm"
                    >
                        @if($breadcrumb->isRoot())
                            <flux:icon name="house" class="size-5"/>
                        @else
                            {{ $breadcrumb->name }}
                        @endif
                    </flux:button>
                @endif
                @if(!$loop->last)
                    <span class="mx-1">/</span>
                @endif
            @endforeach

            @if($allowFolderCreation)
                <span class="mx-1">/</span>

                <flux:modal.trigger name="create-folder-modal">
                    <flux:button
                        square
                        variant="ghost"
                        size="sm"
                        icon="plus"
                        tooltip="Create new folder"
                        class="ml-2"
                    />
                </flux:modal.trigger>
            @endif
        </nav>

        <div>
            {{-- Upload button --}}
            <flux:button
                square
                variant="ghost"
                :icon="$isUploading ? 'loading' : 'upload'"
                tooltip="File Upload"
                class="mr-2"
                onclick="document.getElementById('file-browser-upload-input').click()"
            />

            {{-- Hidden file input for OS file browser --}}
            <input
                id="file-browser-upload-input"
                type="file"
                multiple
                class="hidden"
                onchange="handleFileBrowserUpload(this.files)"
            />

            {{-- View controls --}}
            <flux:dropdown>
                <flux:button
                    square
                    variant="ghost"
                    icon="settings-2"
                    tooltip="View controls"
                />

                <flux:popover class="w-64 space-y-2">
                    {{-- Sort by --}}
                    <div>
                        <flux:label class="text-sm font-medium mb-2">Sort by</flux:label>
                        <flux:select variant="listbox" wire:model.live="sortBy" size="sm">
                            <flux:select.option value="name">Name</flux:select.option>
                            <flux:select.option value="size">Size</flux:select.option>
                            <flux:select.option value="mime_type">Type</flux:select.option>
                            <flux:select.option value="created_at">Date</flux:select.option>
                        </flux:select>
                    </div>
                    {{-- Sort direction and View mode --}}
                    <flux:button.group>
                        <flux:button
                            variant="filled"
                            wire:click.prevent="toggleSortDirection"
                            :icon="$this->sortDirection === 'desc' ? 'arrow-up-narrow-wide' : 'arrow-down-wide-narrow'"
                            tooltip="Toggle sort direction"
                        />
                        <flux:button
                            variant="filled"
                            wire:click="toggleViewMode"
                            :icon="$viewMode !== 'grid' ? 'layout-grid' : 'table'"
                            tooltip="Toggle view mode"
                        />
                    </flux:button.group>
                </flux:popover>
            </flux:dropdown>
        </div>
    </div>

    <flux:separator/>

    {{-- Main content area with drag & drop --}}
    <div
        x-data="{
            dragOver: false
        }"
        @dragover.prevent="dragOver = true"
        @dragleave.prevent="dragOver = false"
        @drop.prevent="
            dragOver = false;
            handleFileBrowserUpload($event.dataTransfer.files);
        "
        :class="{
            'bg-blue-50 dark:bg-blue-900/20': dragOver
        }"
        class="transition-colors duration-200 min-h-96"
    >
        @if($viewMode === 'grid')
        {{-- Grid View --}}
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-6 gap-2 p-3">
            {{-- Folders --}}
            @foreach($this->folders as $folder)
                <div
                    wire:key="folder-{{ $folder->id }}"
                    wire:click="navigateToFolder({{ $folder->id }})"
                    class="flex flex-col items-center p-3 hover:bg-neutral-100 dark:hover:bg-neutral-700 rounded-lg cursor-pointer transition-colors group"
                >
                    <div class="text-blue-500 dark:text-blue-400 mb-2">
                        <flux:icon name="folder" class="size-6"/>
                    </div>
                    <flux:text class="mt-1">
                        {{ $folder->name }}
                    </flux:text>
                </div>
            @endforeach

            {{-- Files --}}
            @foreach($this->files as $file)
                <div
                    wire:click="selectFile({{ $file->id }})"
                    @class(['flex flex-col items-center p-3 hover:bg-neutral-100 dark:hover:bg-neutral-700 rounded-lg cursor-pointer relative border-2 border-transparent', 'border-blue-500 dark:border-blue-400' => $this->selected_file_id === $file->id])
                >
                    <flux:tooltip class="absolute isolate top-0 right-0" toggleable>
                        <flux:button icon="information-circle" size="xs" variant="ghost"/>

                        <flux:tooltip.content class="max-w-[20rem] space-y-2">
                            <div class="flex flex-col">
                                <flux:heading class="inline-flex gap-1 text-xs" level="5">
                                    File Size:
                                    <flux:text class="text-xs">{{ $file->getHumanReadableSize() }}</flux:text>
                                </flux:heading>
                                <flux:heading class="inline-flex gap-1 text-xs" level="5">
                                    Modified:
                                    <flux:text
                                        class="text-xs">
                                        {{ $file->created_at->format(config("flux-files.localization.$locale.formats.datetime", 'd/m/Y H:i:s')) }}
                                    </flux:text>
                                </flux:heading>
                            </div>
                        </flux:tooltip.content>
                    </flux:tooltip>
                    <div class="mb-2">
                        <flux:icon :name="$this->getFileIcon($file)" class="size-6"/>
                    </div>
                    <flux:text class="inline-flex text-center text-wrap truncate">
                        {{ $file->original_name }}
                    </flux:text>
                </div>
            @endforeach

        </div>

    @else
        {{-- Table View --}}
        <div class="px-4">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Size</flux:table.column>
                    <flux:table.column>Type</flux:table.column>
                    <flux:table.column>Modified</flux:table.column>
                    @if($showActions)
                        <flux:table.column></flux:table.column>
                    @endif
                </flux:table.columns>

                <flux:table.rows>
                    {{-- Folders --}}
                    @foreach($this->folders as $folder)
                        <flux:table.row
                            wire:key="folder-row-{{ $folder->id }}"
                            wire:click="navigateToFolder({{ $folder->id }})"
                            class="cursor-pointer"
                        >
                            <flux:table.cell>
                                <div class="flex items-center space-x-2">
                                    <div class="flex-shrink-0">
                                        <flux:icon
                                            name="folder"
                                            class="size-5 text-blue-500 dark:text-blue-400"
                                        />
                                    </div>
                                    <div class="text-sm font-medium">
                                        {{ $folder->name }}
                                    </div>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>—</flux:table.cell>
                            <flux:table.cell>Folder</flux:table.cell>
                            <flux:table.cell>{{ $folder->created_at->format(config("flux-files.localization.$locale.formats.datetime", 'd/m/Y H:i:s')) }}</flux:table.cell>
                            @if($showActions)
                                <flux:table.cell wire:click.stop>
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
                                        <livewire:flux-files.browser.rename-folder :folderId="$folder->id"
                                                                                   :key="'rename-folder-'.$folder->id"/>
                                    </flux:modal>

                                    <flux:modal name="delete-folder-modal-{{$folder->id}}">
                                        <livewire:flux-files.browser.delete-folder :folderId="$folder->id"
                                                                                   :key="'delete-folder-'.$folder->id"/>
                                    </flux:modal>
                                </flux:table.cell>
                            @endif
                        </flux:table.row>
                    @endforeach

                    {{-- Files --}}
                    @foreach($this->files as $file)
                        <flux:table.row
                            wire:key="file-row-{{ $file->id }}"
                            wire:click="selectFile('{{ $file->id }}')"
                            @class(['border-l-4 border-white dark:border-zinc-800 cursor-pointer', 'border-collapse border-l-4 border-l-blue-500 dark:border-l-blue-400' => $this->selected_file_id === $file->id])
                        >
                            <flux:table.cell>
                                <div class="flex items-center space-x-2">
                                    <flux:icon
                                        :name="$this->getFileIcon($file)"
                                        class="size-5"
                                    />
                                    <div class="text-sm font-medium">
                                        {{ $file->name }}
                                    </div>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $file->getHumanReadableSize() }}</flux:table.cell>
                            <flux:table.cell>{{ ucfirst(explode('/', $file->mime_type)[0]) }}</flux:table.cell>
                            <flux:table.cell>{{ $file->created_at->format(config("flux-files.localization.$locale.formats.datetime", 'd/m/Y H:i:s')) }}</flux:table.cell>
                            @if($showActions)
                                <flux:table.cell wire:click.stop>
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
                                            <flux:menu.item disabled>
                                                <flux:icon name="move" class="size-4 mr-2"/>
                                                Move (Not implemented)
                                            </flux:menu.item>
                                        </flux:menu>
                                    </flux:dropdown>
                                    {{-- Modals for file actions --}}
                                    <flux:modal name="rename-file-modal-{{$file->id}}">
                                        <livewire:flux-files.browser.rename-file :fileId="$file->id"
                                                                                 :key="'rename-file-'.$file->id"/>
                                    </flux:modal>

                                    <flux:modal name="delete-file-modal-{{$file->id}}">
                                        <livewire:flux-files.browser.delete-file :fileId="$file->id"
                                                                                 :key="'delete-file-'.$file->id"/>
                                    </flux:modal>
                                </flux:table.cell>
                            @endif
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    @endif

    {{-- Empty state --}}
    @if($this->folders->isEmpty() && $this->files->isEmpty())
        <div class="text-center py-12 my-8">
            <div class="text-neutral-400 dark:text-neutral-500 mb-4">
                <flux:icon name="triangle-alert" class="size-12 mx-auto"/>
            </div>
            <flux:heading level="2">{{ __('No folders or files found') }}</flux:heading>
        </div>
    @endif

    {{-- Pagination --}}
    @if($this->files instanceof \Illuminate\Pagination\LengthAwarePaginator && $this->files->hasPages())
        <div class="mt-4">
            {{ $this->files->links() }}
        </div>
    @endif
    </div>

    {{-- Create Folder Modal --}}
    <flux:modal name="create-folder-modal">
        <livewire:flux-files.browser.create-folder :folder-id="$currentFolderId" :tenant-id="$tenantId"
                                                   :key="'create-folder-modal-'.$this->currentFolderId"/>
    </flux:modal>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        // Handle file uploads for both OS file browser and drag & drop
        window.handleFileBrowserUpload = function(files) {
            if (!files || files.length === 0) {
                return;
            }

            // Convert FileList to Array
            const fileArray = Array.from(files);

            // Start upload state
            Livewire.dispatch('start-upload');

            // Use Livewire's built-in upload functionality
            for (let i = 0; i < fileArray.length; i++) {
                @this.upload('tempFile', fileArray[i], (uploadedFilename) => {
                    // File uploaded successfully - call backend to process it
                    @this.call('processUploadedFile', uploadedFilename);
                }, () => {
                    // Upload failed - end upload state
                    Livewire.dispatch('end-upload');
                    console.error('Upload failed for file:', fileArray[i].name);
                });
            }

            // Reset the file input to allow uploading the same file again
            const fileInput = document.getElementById('file-browser-upload-input');
            if (fileInput) {
                fileInput.value = '';
            }
        };
    });
</script>
