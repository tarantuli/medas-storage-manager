<?php

declare(strict_types=1);

namespace Medas\StorageManager;

use Medas\Cache\MemoryCache;
use Medas\Core\Attributes\Service;
use Medas\EntityManager\{MetaData, MetaDataManager};

#[Service]
readonly class PropertyStoreMapper
{
    private MemoryCache $cache;

    public function __construct(
        private MetaDataManager $metaDataManager,
    )
    {
        $this->cache = new MemoryCache();
    }

    public function get(MetaData $metaData): PropertyStoreMap
    {
        return $this->cache->get($metaData->className, fn() => $this->build($metaData));
    }

    private function build(MetaData $metaData): PropertyStoreMap
    {
        $chain = $this->buildChain($metaData);
        $classIndex = array_flip(array_column($chain, 0));
        $map = [];

        foreach ($metaData->properties as $property) {
            $declaringClass = $property->reflection->getDeclaringClass()->name;
            $map[$property->name] = $this->resolveStore($chain, $classIndex, $declaringClass);
        }

        return new PropertyStoreMap($map);
    }

    private function buildChain(MetaData $metaData): array
    {
        // Build an ordered list of [className, store] pairs starting from the entity itself,
        // walking up to each ancestor. Index 0 is the entity class, higher indices are ancestors.
        $chain = [];

        do {
            $chain[] = [$metaData->className, $metaData->entity->store];
            $parentClass = $metaData->inheritance->parent;
            $metaData = $parentClass !== null ? $this->metaDataManager->get($parentClass) : null;
        } while ($metaData !== null);

        return $chain;
    }

    private function resolveStore(array $chain, array $classIndex, string $className): string|null
    {
        // Start at the declaring class and walk toward the entity (index 0) to find the first
        // non-null store. This handles ancestor classes that have no store of their own.
        $index = $classIndex[$className] ?? 0;

        while ($index >= 0) {
            if ($chain[$index][1] !== null) {
                return $chain[$index][1];
            }

            --$index;
        }

        return null;
    }
}
