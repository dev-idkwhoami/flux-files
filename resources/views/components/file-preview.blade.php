@props(['file', 'size' => 'md'])

@php
    $isImage = $file && str_starts_with($file->mime_type ?? '', 'image/');
    $sizeClasses = [
        'sm' => 'size-8',
        'md' => 'size-16',
        'lg' => 'size-32',
        'xl' => 'size-48'
    ];
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

<div {{ $attributes->merge(['class' => "flex items-center justify-center $sizeClass"]) }}>
    @if($isImage && method_exists($file, 'getUrl'))
        <img
            src="{{ $file->getUrl() }}"
            alt="{{ $file->original_name ?? $file->name ?? 'File preview' }}"
            class="object-cover rounded {{ $sizeClass }}"
            loading="lazy"
        />
    @else
        <x-flux-files::file-icon :file="$file" :class="$sizeClass" />
    @endif
</div>
