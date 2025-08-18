<div class="flux-files-browser">
    {{-- Header with breadcrumbs and controls --}}
    <div class="flex items-center justify-between mb-4 p-3 bg-neutral-50 dark:bg-neutral-800 rounded-lg">
        {{-- Breadcrumbs --}}
        <nav class="flex items-center space-x-1 text-sm">
            @foreach($this->breadcrumbs as $breadcrumb)
                @if($loop->last)
                    <span class="text-neutral-600 dark:text-neutral-300 font-medium flex items-center">
                        @if($breadcrumb->isRoot())
                            <flux:button
                                disabled
                                variant="ghost"
                                size="sm"
                            >
                                <flux:icon name="house" class="size-4 mr-1"/>
                            </flux:button>
                        @endif
                    </span>
                @elseif($breadcrumb->isEllipsis)
                    <span class="text-neutral-400 px-2">{{ $breadcrumb->name }}</span>
                @else
                    <flux:button
                        wire:click="navigateToFolder({{ $breadcrumb->id }})"
                        variant="ghost"
                        size="sm"
                    >
                        @if($breadcrumb->isRoot())
                            <flux:icon name="house" class="size-4 mr-1"/>
                        @else
                            <span>{{ $breadcrumb->name }}</span>
                        @endif
                    </flux:button>
                    @if(!$loop->last)
                        <span class="text-neutral-400 px-1">/</span>
                    @endif
                @endif
            @endforeach
        </nav>

        {{-- View controls --}}
        <div class="flex items-center space-x-2">
            {{-- Sort dropdown --}}
            <flux:select variant="listbox" wire:model.live="sortBy" size="sm" class="w-fit">
                <flux:select.option value="name">Name</flux:select.option>
                <flux:select.option value="size">Size</flux:select.option>
                <flux:select.option value="mime_type">Type</flux:select.option>
                <flux:select.option value="created_at">Date</flux:select.option>
            </flux:select>

            {{-- Sort direction --}}
            <flux:button
                square
                variant="ghost"
                wire:click.prevent="toggleSortDirection"
                :icon="$this->sortDirection === 'desc' ? 'arrow-up-narrow-wide' : 'arrow-down-wide-narrow'"
                tooltip="Toggle sort direction"
            />

            {{-- View mode toggle --}}
            <flux:button
                square
                variant="ghost"
                wire:click="toggleViewMode"
                :icon="$viewMode === 'grid' ? 'layout-grid' : 'table'"
                tooltip="Toggle view mode"
            />
        </div>
    </div>

    {{-- Main content area --}}
    <div class="border border-neutral-200 dark:border-neutral-700 rounded-lg overflow-hidden">
        @if($viewMode === 'grid')
            {{-- Grid View --}}
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-2 p-3">
                {{-- Folders --}}
                @foreach($this->folders as $folder)
                    <div
                        wire:click="navigateToFolder({{ $folder->id }})"
                        class="flex flex-col items-center p-3 hover:bg-neutral-100 dark:hover:bg-neutral-700 rounded-lg cursor-pointer transition-colors group"
                    >
                        <div class="text-blue-500 dark:text-blue-400 mb-2">
                            <flux:icon name="folder" class="size-8"/>
                        </div>
                        <span
                            class="text-xs text-center text-neutral-700 dark:text-neutral-300 group-hover:text-neutral-900 dark:group-hover:text-neutral-100 truncate w-full"
                            title="{{ $folder->name }}">
                            {{ $folder->name }}
                        </span>
                    </div>
                @endforeach

                {{-- Files --}}
                @foreach($this->files as $file)
                    <div
                        wire:click="selectFile({{ $file->id }})"
                        class="flex flex-col items-center p-3 hover:bg-neutral-100 dark:hover:bg-neutral-700 rounded-lg cursor-pointer transition-colors group"
                    >
                        <div class="mb-2">
                            <div
                                class="w-8 h-8 bg-neutral-200 dark:bg-neutral-600 rounded flex items-center justify-center">
                                <flux:icon :name="$this->getFileIcon($file)" class="size-5 text-neutral-500"/>
                            </div>
                        </div>
                        <span
                            class="text-xs text-center text-neutral-700 dark:text-neutral-300 group-hover:text-neutral-900 dark:group-hover:text-neutral-100 truncate w-full"
                            title="{{ $file->original_name }}">
                            {{ $file->original_name }}
                        </span>
                        <span class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                            {{ $file->getHumanReadableSize() }}
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Table View --}}
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead
                        class="bg-neutral-50 dark:bg-neutral-800 border-b border-neutral-200 dark:border-neutral-700">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                            Name
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                            Size
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                            Type
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                            Modified
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-neutral-900 divide-y divide-neutral-200 dark:divide-neutral-700">
                    {{-- Folders --}}
                    @foreach($this->folders as $folder)
                        <tr
                            wire:click="navigateToFolder({{ $folder->id }})"
                            class="hover:bg-neutral-50 dark:hover:bg-neutral-800 cursor-pointer"
                        >
                            <td class="px-4 py-2 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 mr-3">
                                        <flux:icon name="folder" class="size-5 text-blue-500 dark:text-blue-400"/>
                                    </div>
                                    <div class="text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                        {{ $folder->name }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">
                                —
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">
                                Folder
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">
                                {{ $folder->created_at->format('M j, Y g:i A') }}
                            </td>
                        </tr>
                    @endforeach

                    {{-- Files --}}
                    @foreach($this->files as $file)
                        <tr
                            wire:click="selectFile({{ $file->id }})"
                            class="hover:bg-neutral-50 dark:hover:bg-neutral-800 cursor-pointer"
                        >
                            <td class="px-4 py-2 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 mr-3">
                                        <div
                                            class="w-5 h-5 bg-neutral-200 dark:bg-neutral-600 rounded flex items-center justify-center">
                                            <flux:icon :name="$this->getFileIcon($file)"
                                                       class="size-3 text-neutral-500"/>
                                        </div>
                                    </div>
                                    <div class="text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                        {{ $file->original_name }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">
                                {{ $file->getHumanReadableSize() }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">
                                {{ ucfirst(explode('/', $file->mime_type)[0]) }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">
                                {{ $file->created_at->format('M j, Y g:i A') }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
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
    </div>

    {{-- Pagination --}}
    @if($this->files instanceof \Illuminate\Pagination\LengthAwarePaginator && $this->files->hasPages())
        <div class="mt-4">
            {{ $this->files->links() }}
        </div>
    @endif
</div>
