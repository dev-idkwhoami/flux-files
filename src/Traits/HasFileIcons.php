<?php

namespace Idkwhoami\FluxFiles\Traits;

use Idkwhoami\FluxFiles\Models\File;

trait HasFileIcons
{
    public function getFileIcon(File $file): string
    {
        $icons = config('flux-files.ui.file_icons');

        if ($file->isImage()) {
            return $icons['image'] ?? 'file-question-mark';
        }

        if ($file->isVideo()) {
            return $icons['video'] ?? 'file-question-mark';
        }

        if ($file->isAudio()) {
            return $icons['audio'] ?? 'file-question-mark';
        }

        // Check for archive files by extension
        $extension = strtolower(pathinfo($file->original_name, PATHINFO_EXTENSION));
        if (in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz'])) {
            return $icons['archive'] ?? 'file-question-mark';
        }

        // Check for document files
        if (str_starts_with($file->mime_type, 'application/') || str_starts_with($file->mime_type, 'text/')) {
            return $icons['document'] ?? 'file-question-mark';
        }

        return $icons['default'] ?? 'file-question-mark';
    }
}
