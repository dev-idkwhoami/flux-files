<?php

namespace Idkwhoami\FluxFiles\Models;

use Idkwhoami\FluxFiles\Traits\Eloquent\HasFluxFilesId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use HasFluxFilesId;

    protected $fillable = [
        'name',
        'original_name',
        'path',
        'disk',
        'mime_type',
        'size',
        'folder_id',
        'tenant_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'size' => 'integer',
    ];

    /**
     * Get the folder that owns the file.
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Get the tenant that owns the file (if multi-tenancy is enabled).
     */
    public function tenant(): BelongsTo
    {
        $tenantModel = config('flux-files.tenancy.model');
        return $this->belongsTo($tenantModel);
    }

    /**
     * Scope a query to only include files for a specific tenant.
     */
    public function scopeByTenant(Builder $query, $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope a query to only include files with a specific mime type.
     */
    public function scopeByMimeType(Builder $query, string $mimeType): Builder
    {
        return $query->where('mime_type', $mimeType);
    }

    /**
     * Scope a query to only include files in a specific folder.
     */
    public function scopeInFolder(Builder $query, $folderId): Builder
    {
        return $query->where('folder_id', $folderId);
    }

    /**
     * Get the URL to the file.
     */
    public function getUrl(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Get the preview URL for the file (typically for images).
     */
    public function getPreviewUrl(): ?string
    {
        if (!$this->isImage()) {
            return null;
        }

        // For now, return the same URL. In future, this could return thumbnail URL
        return $this->getUrl();
    }

    /**
     * Check if the file is an image.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if the file is an audio file.
     */
    public function isAudio(): bool
    {
        return str_starts_with($this->mime_type, 'audio/');
    }

    /**
     * Check if the file is a video file.
     */
    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    /**
     * Get the human-readable size of the file.
     */
    public function getHumanReadableSize(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
