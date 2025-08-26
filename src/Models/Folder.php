<?php

namespace Idkwhoami\FluxFiles\Models;

use Idkwhoami\FluxFiles\Traits\Eloquent\HasFluxFilesId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class Folder extends Model
{
    use HasFluxFilesId;

    protected $fillable = [
        'name',
        'path',
        'parent_id',
        'tenant_id',
    ];

    /**
     * Get the files that belong to the folder.
     */
    public function files(): HasMany
    {
        return $this->hasMany(config('flux-files.eloquent.file.model', File::class));
    }

    /**
     * Get the child folders.
     */
    public function children(): HasMany
    {
        return $this->hasMany(config('flux-files.eloquent.folder.model', Folder::class), 'parent_id');
    }

    /**
     * Get the parent folder.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(config('flux-files.eloquent.folder.model', Folder::class), 'parent_id');
    }

    /**
     * Get the tenant that owns the folder (if multi-tenancy is enabled).
     */
    public function tenant(): BelongsTo
    {
        $tenantModel = config('flux-files.tenancy.model');
        return $this->belongsTo($tenantModel);
    }

    /**
     * Scope a query to only include folders for a specific tenant.
     */
    public function scopeByTenant(Builder $query, $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope a query to only include root folders.
     */
    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope a query to include folders with their depth calculated.
     */
    public function scopeWithDepth(Builder $query): Builder
    {
        // This is a simplified implementation
        // For more complex depth calculations, consider using packages like kalnoy/nestedset
        return $query->selectRaw('*, (LENGTH(path) - LENGTH(REPLACE(path, "/", ""))) as depth');
    }

    /**
     * Get the full path of the folder.
     */
    public function getFullPath(): string
    {
        if (!$this->parent_id) {
            return $this->path;
        }

        $parentPath = $this->parent ? $this->parent->getFullPath() : '';
        return $parentPath . '/' . $this->path;
    }

    /**
     * Check if this folder is an ancestor of the given folder.
     */
    public function isAncestorOf(Folder $folder): bool
    {
        if ($folder->parent_id === $this->id) {
            return true;
        }

        if ($folder->parent) {
            return $this->isAncestorOf($folder->parent);
        }

        return false;
    }

    /**
     * Get all descendant folders.
     */
    public function getDescendants(): Collection
    {
        $descendants = collect();

        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getDescendants());
        }

        return $descendants;
    }

    /**
     * Create a subfolder within this folder.
     */
    public function createSubfolder(string $name): Folder
    {
        $subfolder = new Folder([
            'name' => $name,
            'path' => $this->getFullPath() . '/' . $name,
            'parent_id' => $this->id,
            'tenant_id' => $this->tenant_id,
        ]);

        $subfolder->save();

        return $subfolder;
    }
}
