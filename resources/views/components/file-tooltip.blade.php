@props(['file'])

<flux:tooltip {{ $attributes->merge(['class' => 'absolute isolate top-0 right-0']) }} toggleable>
    <flux:button icon="information-circle" size="xs" variant="ghost"/>

    <flux:tooltip.content class="max-w-[20rem] space-y-2">
        <div class="flex flex-col">
            <flux:heading class="inline-flex gap-1 text-xs" level="5">
                File Size:
                <flux:text class="text-xs">
                    <x-flux-files::file-size :bytes="$file->size ?? 0" />
                </flux:text>
            </flux:heading>
            <flux:heading class="inline-flex gap-1 text-xs" level="5">
                Modified:
                <flux:text class="text-xs">
                    <x-flux-files::file-date :date="$file->created_at ?? $file->updated_at ?? now()" />
                </flux:text>
            </flux:heading>
            @if(isset($file->mime_type))
                <flux:heading class="inline-flex gap-1 text-xs" level="5">
                    Type:
                    <flux:text class="text-xs">
                        {{ $file->mime_type }}
                    </flux:text>
                </flux:heading>
            @endif
        </div>
    </flux:tooltip.content>
</flux:tooltip>
