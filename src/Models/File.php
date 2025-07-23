<?php

namespace App\Models;

use Idkwhoami\FluxFiles\Traits\Eloquent\HasFluxFilesId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class File
 *
 * @property string $id
 * @property string $name
 * @property string $original_name
 * @property string $path
 * @property string $disk
 * @property string $mime_type
 * @property int $size
 * @property string $folder_id
 * @property string|null $tenant_id
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Folder $folder
 */
class File extends Model
{
    use HasFluxFilesId;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }
}
