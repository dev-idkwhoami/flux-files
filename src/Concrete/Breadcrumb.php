<?php

namespace Idkwhoami\FluxFiles\Concrete;

final readonly class Breadcrumb
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $path,
        public bool $isEllipsis = false
    ) {
    }

    public static function root(): self
    {
        return new self(
            id: null,
            name: 'Root',
            path: '/'
        );
    }

    public static function folder(int $id, string $name, string $path): self
    {
        return new self(
            id: $id,
            name: $name,
            path: $path
        );
    }

    public static function ellipsis(): self
    {
        return new self(
            id: null,
            name: '...',
            path: '',
            isEllipsis: true
        );
    }

    public function isRoot(): bool
    {
        return $this->id === null && $this->name === 'Root';
    }

    public function isClickable(): bool
    {
        return !$this->isEllipsis;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'path' => $this->path,
            'isEllipsis' => $this->isEllipsis,
            'isRoot' => $this->isRoot(),
            'isClickable' => $this->isClickable(),
        ];
    }
}
