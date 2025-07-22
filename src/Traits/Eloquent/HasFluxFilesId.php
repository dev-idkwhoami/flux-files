<?php

namespace Idkwhoami\FluxFiles\Traits\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Uid\Ulid;

/**
 * @mixin Model
 */
trait HasFluxFilesId
{
    protected static function bootHasDynamicIdColumn(): void
    {
        static::creating(function ($model) {
            $idType = config('flux-files.eloquent.id_type', 'bigint');

            if (in_array($idType, ['ulid', 'uuid']) && empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = $model->newUniqueId();
            }
        });
    }

    public function initializeHasDynamicIdColumn(): void
    {
        $idType = config('flux-files.eloquent.id_type', 'bigint');

        if (in_array($idType, ['ulid', 'uuid'])) {
            $this->setKeyType('string');
            $this->setIncrementing(false);
        }
    }

    public function newUniqueId(): string
    {
        $idType = config('flux-files.eloquent.id_type', 'bigint');

        return match($idType) {
            'ulid' => Ulid::generate(),
            'uuid' => Uuid::uuid4()->toString(),
            default => throw new \InvalidArgumentException('Cannot generate unique ID for bigint type'),
        };
    }

    public function getIncrementing(): bool
    {
        $idType = config('flux-files.eloquent.id_type', 'bigint');

        return $idType === 'bigint';
    }

    public function getKeyType(): string
    {
        $idType = config('flux-files.eloquent.id_type', 'bigint');

        return match($idType) {
            'ulid', 'uuid' => 'string',
            default => 'int',
        };
    }



}
