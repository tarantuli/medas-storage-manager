<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\Cache\MemoryCache;
use Medas\Core\{Attributes\Service, Serializers\NoopSerializer};
use Medas\EntityManager\MetaData;
use Medas\StorageManager\{Interfaces\Store, StorageManager, Structure\EntityStructureFinder};

#[Service]
readonly class StoresFinder
{
    private MemoryCache $cache;

    public function __construct(
        private EntityStructureFinder $entityStructureFinder,
        private StorageManager        $storageManager,
    )
    {
        $this->cache = new MemoryCache(new NoopSerializer());
    }

    /** @return Store[] */
    public function find(MetaData $metaData): array
    {
        return $this->cache->get($metaData->className, fn() => $this->gather($metaData));
    }

    /** @return Store[] */
    private function gather(MetaData $metaData): array
    {
        $blueprint = $this->entityStructureFinder->find($metaData->className);
        $storeNames = [];

        foreach ($blueprint->fields as $field) {
            $storeNames[] = $field->store;
        }

        $stores = [];

        foreach (array_unique($storeNames) as $name) {
            $stores[] = $this->storageManager->controller($metaData->entity->storage)->store($name);
        }

        return $stores;
    }
}
