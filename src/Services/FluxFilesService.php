<?php

namespace Idkwhoami\FluxFiles\Services;

use Carbon\Carbon;
use DateTime;

class FluxFilesService
{
    /**
     * Get appropriate icon for file based on MIME type and extension
     */
    public function icon($file): string
    {
        if (!$file) {
            return 'document';
        }

        $mimeType = $file->mime_type ?? '';
        $extension = strtolower(pathinfo($file->original_name ?? $file->name ?? '', PATHINFO_EXTENSION));

        // Image files
        if (str_starts_with($mimeType, 'image/')) {
            return 'photo';
        }
        // Video files
        elseif (str_starts_with($mimeType, 'video/')) {
            return 'video-camera';
        }
        // Audio files
        elseif (str_starts_with($mimeType, 'audio/')) {
            return 'musical-note';
        }
        // PDF files
        elseif ($mimeType === 'application/pdf') {
            return 'document-text';
        }
        // Archive files
        elseif (in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz'])) {
            return 'archive-box';
        }
        // Text/Code files
        elseif (str_starts_with($mimeType, 'text/') || in_array($extension, ['php', 'js', 'css', 'html', 'json', 'xml'])) {
            return 'code-bracket';
        }
        // Spreadsheet files
        elseif (in_array($extension, ['xls', 'xlsx', 'csv'])) {
            return 'table-cells';
        }
        // Word documents
        elseif (in_array($extension, ['doc', 'docx'])) {
            return 'document-text';
        }
        // PowerPoint files
        elseif (in_array($extension, ['ppt', 'pptx'])) {
            return 'presentation-chart-bar';
        }

        return 'document';
    }

    /**
     * Format bytes to human readable string
     */
    public function formatSize($bytes): string
    {
        $bytes = (int) $bytes;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        if ($bytes === 0) {
            return '0 B';
        }

        $power = floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);
        $size = $bytes / pow(1024, $power);

        return round($size, $power > 0 ? 2 : 0) . ' ' . $units[$power];
    }

    /**
     * Format date according to locale settings
     */
    public function formatDate($date): string
    {
        $locale = app()->getLocale();
        $format = config("flux-files.localization.$locale.formats.datetime", 'd/m/Y H:i:s');

        if ($date instanceof Carbon || $date instanceof DateTime) {
            return $date->format($format);
        } elseif (is_string($date)) {
            return Carbon::parse($date)->format($format);
        }

        return now()->format($format);
    }

    /**
     * Check if file type is allowed
     */
    public function validateType($file, array $allowedTypes = []): bool
    {
        if (empty($allowedTypes)) {
            return true;
        }

        $extension = strtolower(pathinfo($file->original_name ?? $file->name ?? '', PATHINFO_EXTENSION));
        $mimeType = $file->mime_type ?? '';

        // Check by extension
        if (in_array($extension, $allowedTypes)) {
            return true;
        }

        // Check by MIME type
        foreach ($allowedTypes as $allowedType) {
            if (str_starts_with($mimeType, $allowedType)) {
                return true;
            }
        }

        return false;
    }
}
