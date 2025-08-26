@props(['sortBy' => 'name', 'sortDirection' => 'asc', 'onSortChange' => null, 'onDirectionToggle' => null])

<div {{ $attributes->merge(['class' => 'w-64 space-y-2']) }}>
    {{-- Sort by --}}
    <div>
        <flux:label class="text-sm font-medium mb-2">Sort by</flux:label>
        <flux:select
            variant="listbox"
            @if($onSortChange)
                wire:model.live="{{ $onSortChange }}"
            @endif
            size="sm"
            :value="$sortBy"
        >
            <flux:select.option value="name">Name</flux:select.option>
            <flux:select.option value="size">Size</flux:select.option>
            <flux:select.option value="mime_type">Type</flux:select.option>
            <flux:select.option value="created_at">Date</flux:select.option>
        </flux:select>
    </div>

    {{-- Sort direction --}}
    <flux:button.group>
        <flux:button
            variant="filled"
            @if($onDirectionToggle)
                wire:click.prevent="{{ $onDirectionToggle }}"
            @endif
            :icon="$sortDirection === 'desc' ? 'arrow-up-narrow-wide' : 'arrow-down-wide-narrow'"
            tooltip="Toggle sort direction"
        />
        <x-flux-files::view-mode-toggle />
    </flux:button.group>
</div>
