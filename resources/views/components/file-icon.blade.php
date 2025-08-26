@php
    $iconName = 'document';

    if ($file) {
        $mimeType = $file->mime_type ?? '';
        $extension = strtolower(pathinfo($file->original_name ?? $file->name ?? '', PATHINFO_EXTENSION));

        // Image files
        if (str_starts_with($mimeType, 'image/')) {
            $iconName = 'photo';
        }
        // Video files
        elseif (str_starts_with($mimeType, 'video/')) {
            $iconName = 'video-camera';
        }
        // Audio files
        elseif (str_starts_with($mimeType, 'audio/')) {
            $iconName = 'musical-note';
        }
        // PDF files
        elseif ($mimeType === 'application/pdf') {
            $iconName = 'document-text';
        }
        // Archive files
        elseif (in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz'])) {
            $iconName = 'archive-box';
        }
        // Text/Code files
        elseif (str_starts_with($mimeType, 'text/') || in_array($extension, ['php', 'js', 'css', 'html', 'json', 'xml'])) {
            $iconName = 'code-bracket';
        }
        // Spreadsheet files
        elseif (in_array($extension, ['xls', 'xlsx', 'csv'])) {
            $iconName = 'table-cells';
        }
        // Word documents
        elseif (in_array($extension, ['doc', 'docx'])) {
            $iconName = 'document-text';
        }
        // PowerPoint files
        elseif (in_array($extension, ['ppt', 'pptx'])) {
            $iconName = 'presentation-chart-bar';
        }
    }
@endphp

<flux:icon :name="$iconName" {{ $attributes->merge(['class' => 'size-6']) }} />
