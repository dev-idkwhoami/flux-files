@props(['isUploading' => false, 'inputId' => 'file-upload-input', 'onClick' => null])

<flux:button
    square
    variant="ghost"
    :icon="$isUploading ? 'loading' : 'upload'"
    tooltip="File Upload"
    @if($onClick)
        onclick="{{ $onClick }}"
    @else
        onclick="document.getElementById('{{ $inputId }}').click()"
    @endif
    {{ $attributes->merge(['class' => 'mr-2']) }}
/>
