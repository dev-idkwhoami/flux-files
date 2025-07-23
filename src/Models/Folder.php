<?php

namespace App\Models;

use Idkwhoami\FluxFiles\Traits\Eloquent\HasFluxFilesId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Folder
 *
 * @property string $id
 * @property string $name
 * @property string $path
 * @property string|null $parent_id
 * @property string|null $tenant_id
 * @property bool $is_root
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\File[] $files
 * @property-read \App\Models\Folder|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Folder[] $children
 */
class Folder extends Model
{
    use HasFluxFilesId;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'path',
        'parent_id',
        'tenant_id',
        'is_root',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_root' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }
}
