<?php

namespace Idkwhoami\FluxFiles\Services;

use Idkwhoami\FluxFiles\Enums\FileExtension;
use Idkwhoami\FluxFiles\Enums\MimeType;
use Illuminate\Http\UploadedFile;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class FileValidationService
{
    public function validateFileType(UploadedFile|TemporaryUploadedFile $file, ?array $allowedExtensions = null): bool
    {
        $allowedExtensions = $allowedExtensions ?? config('flux-files.validation.allowed_extensions', []);

        if (empty($allowedExtensions)) {
            return true;
        }

        $extension = strtolower($file->getClientOriginalExtension());
        return in_array($extension, array_map('strtolower', $allowedExtensions));
    }

    public function validateFileSize(UploadedFile|TemporaryUploadedFile $file, ?int $maxSizeInBytes = null): bool
    {
        $maxSizeInBytes = $maxSizeInBytes ?? config('flux-files.validation.max_file_size', 10485760);

        return $file->getSize() <= $maxSizeInBytes;
    }

    public function validateFolderRestrictions(?int $folderId = null, ?int $tenantId = null): bool
    {
        $folderRestrictions = config('flux-files.validation.folder_restrictions', []);

        if (empty($folderRestrictions)) {
            return true;
        }

        if ($tenantId && isset($folderRestrictions['tenant_folders'])) {
            $allowedFolders = $folderRestrictions['tenant_folders'][$tenantId] ?? [];
            if (!empty($allowedFolders) && !in_array($folderId, $allowedFolders)) {
                return false;
            }
        }

        if (isset($folderRestrictions['restricted_folders']) && in_array($folderId, $folderRestrictions['restricted_folders'])) {
            return false;
        }

        return true;
    }

    public function validateMimeType(UploadedFile|TemporaryUploadedFile $file): bool
    {
        $detectedMimeType = $file->getMimeType();

        // For TemporaryUploadedFile, we may not have getClientMimeType method, so we'll skip the comparison
        if ($file::class === UploadedFile::class) {
            $expectedMimeType = $file->getClientMimeType();

            if ($detectedMimeType !== $expectedMimeType) {
                return false;
            }
        }

        $allowedMimeTypes = config('flux-files.validation.allowed_mime_types', []);

        if (!empty($allowedMimeTypes)) {
            return in_array($detectedMimeType, $allowedMimeTypes);
        }

        $blockedMimeTypes = config('flux-files.validation.blocked_mime_types', MimeType::blockedMimeTypes());

        return !in_array($detectedMimeType, $blockedMimeTypes);
    }

    public function validateFile(UploadedFile|TemporaryUploadedFile $file, ?int $folderId = null, ?int $tenantId = null, array $customRules = []): array
    {
        $errors = [];

        if (!$this->validateFileType($file, $customRules['allowed_extensions'] ?? null)) {
            $errors[] = 'File type not allowed';
        }

        if (!$this->validateFileSize($file, $customRules['max_file_size'] ?? null)) {
            $errors[] = 'File size exceeds maximum allowed size';
        }

        if (!$this->validateFolderRestrictions($folderId, $tenantId)) {
            $errors[] = 'Upload to this folder is not allowed';
        }

        if (!$this->validateMimeType($file)) {
            $errors[] = 'File type is not secure or allowed';
        }

        return $errors;
    }

    public function isValid(UploadedFile|TemporaryUploadedFile $file, ?int $folderId = null, ?int $tenantId = null, array $customRules = []): bool
    {
        return empty($this->validateFile($file, $folderId, $tenantId, $customRules));
    }

    protected function isExecutableFile(UploadedFile $file): bool
    {
        $executableExtensions = FileExtension::executableExtensions();
        $extension = strtolower($file->getClientOriginalExtension());

        return in_array($extension, $executableExtensions);
    }

    protected function scanForMalware(UploadedFile $file): bool
    {
        $content = file_get_contents($file->getPathname());

        $malwareSignatures = [
            '<?php',
            '<%',
            '<script',
            'javascript:',
            'vbscript:',
            'onload=',
            'onerror=',
        ];

        foreach ($malwareSignatures as $signature) {
            if (stripos($content, $signature) !== false) {
                return false;
            }
        }

        return true;
    }
}
