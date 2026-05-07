<?php

declare(strict_types=1);

namespace Medas\StorageManager;

readonly class PropertyStoreMap
{
    public function __construct(
        // property name => store name
        private array $map,
    )
    {
    }

    public function storeForProperty(string $propertyName): string|null
    {
        return $this->map[$propertyName] ?? null;
    }

    /** @return string[] */
    public function stores(): array
    {
        return array_values(array_unique(array_filter($this->map)));
    }
}
