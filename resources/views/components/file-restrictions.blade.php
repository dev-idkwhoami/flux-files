@props(['allowedTypes' => [], 'maxFileSize' => null, 'maxFiles' => null, 'multiple' => true])

@if(!empty($allowedTypes) || $maxFileSize || $maxFiles)
    <div {{ $attributes->merge(['class' => 'mt-3 text-xs text-gray-400 space-y-1']) }}>
        @if(!empty($allowedTypes))
            <div>Allowed types: {{ implode(', ', $allowedTypes) }}</div>
        @endif

        @if($maxFileSize)
            <div>File Size: < <x-flux-files::file-size :bytes="$maxFileSize" /></div>
        @endif

        @if($maxFiles && $multiple)
            <div>Max files: {{ $maxFiles }}</div>
        @endif
    </div>
@endif
