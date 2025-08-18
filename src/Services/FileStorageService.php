<?php

namespace Idkwhoami\FluxFiles\Services;

use Idkwhoami\FluxFiles\Enums\MimeType;
use Idkwhoami\FluxFiles\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Nette\Utils\Image;

class FileStorageService
{
    public function store(UploadedFile $file, ?int $folderId = null, ?string $disk = null, ?int $tenantId = null): File
    {
        $disk = $disk ?? config('filesystems.default');

        $filename = Str::ulid() . '.' . $file->getClientOriginalExtension();

        $storagePath = $this->getStoragePath($tenantId);
        $fullPath = $storagePath . '/' . $filename;

        $path = $file->storeAs($storagePath, $filename, $disk);

        $fileModel = new File([
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'disk' => $disk,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'folder_id' => $folderId,
            'tenant_id' => $tenantId,
            'metadata' => $this->extractMetadata($file),
        ]);

        $fileModel->save();

        if ($this->isImage($file->getMimeType())) {
            $this->generateThumbnail($fileModel, $disk);
        }

        return $fileModel;
    }

    public function delete(File $file): bool
    {
        if (Storage::disk($file->disk)->exists($file->path)) {
            Storage::disk($file->disk)->delete($file->path);
        }

        $thumbnailPath = $this->getThumbnailPath($file->path);
        if (Storage::disk($file->disk)->exists($thumbnailPath)) {
            Storage::disk($file->disk)->delete($thumbnailPath);
        }

        return $file->delete();
    }

    public function move(File $file, ?int $newFolderId = null): bool
    {
        $file->folder_id = $newFolderId;
        return $file->save();
    }

    public function copy(File $file, ?int $newFolderId = null, ?string $newName = null): File
    {
        $newFile = $file->replicate();
        $newFile->folder_id = $newFolderId;
        $newFile->name = $newName ?? $file->name . ' (Copy)';
        $newFile->save();

        return $newFile;
    }

    public function exists(File $file): bool
    {
        return Storage::disk($file->disk)->exists($file->path);
    }

    protected function getStoragePath(?int $tenantId = null): string
    {
        if (config('flux-files.tenancy.enabled') && $tenantId) {
            return 'tenants/' . $tenantId;
        }

        return 'files';
    }

    protected function extractMetadata(UploadedFile $file): array
    {
        $metadata = [];

        if ($this->isImage($file->getMimeType())) {
            try {
                $image = Image::fromFile($file->getPathname());
                $metadata['width'] = $image->getWidth();
                $metadata['height'] = $image->getHeight();
            } catch (\Exception $e) {
            }
        }

        return $metadata;
    }

    protected function generateThumbnail(File $file, string $disk): void
    {
        if (!$this->isImage($file->mime_type)) {
            return;
        }

        try {
            $originalPath = Storage::disk($disk)->path($file->path);

            $image = Image::fromFile($originalPath);

            $maxWidth = config('flux-files.thumbnails.max_width', 300);
            $maxHeight = config('flux-files.thumbnails.max_height', 300);
            $originalWidth = $image->getWidth();
            $originalHeight = $image->getHeight();

            if ($originalWidth > $originalHeight) {
                $thumbnailWidth = $maxWidth;
                $thumbnailHeight = (int) round(($originalHeight * $maxWidth) / $originalWidth);
            } else {
                $thumbnailHeight = $maxHeight;
                $thumbnailWidth = (int) round(($originalWidth * $maxHeight) / $originalHeight);
            }

            $image->resize($thumbnailWidth, $thumbnailHeight, Image::FIT | Image::SHRINK_ONLY);

            $thumbnailPath = $this->getThumbnailPath($file->path);
            $thumbnailFullPath = Storage::disk($disk)->path($thumbnailPath);

            $thumbnailDir = dirname($thumbnailFullPath);
            if (!is_dir($thumbnailDir)) {
                mkdir($thumbnailDir, 0755, true);
            }

            $image->save($thumbnailFullPath);

        } catch (\Exception $e) {
        }
    }

    protected function getThumbnailPath(string $originalPath): string
    {
        $pathInfo = pathinfo($originalPath);
        return $pathInfo['dirname'] . '/thumbnails/' . $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
    }

    protected function isImage(string $mimeType): bool
    {
        return in_array($mimeType, MimeType::imageMimeTypes());
    }
}
