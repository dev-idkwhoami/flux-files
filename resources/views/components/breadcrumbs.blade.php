@props(['breadcrumbs', 'allowFolderCreation' => false, 'onNavigate' => null])

<nav {{ $attributes->merge(['class' => 'flex items-center text-sm']) }}>
    @foreach($breadcrumbs as $breadcrumb)
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
                @if($onNavigate)
                    wire:click="{{ $onNavigate }}({{ $breadcrumb->id }})"
                @endif
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
