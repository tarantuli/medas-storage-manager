<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\Cache\MemoryCache;
use Medas\Core\Attributes\Service;
use Medas\EntityManager\MetaData;
use Medas\StorageManager\{Interfaces\Store, PropertyStoreMapper, StorageManager};

#[Service]
readonly class StoresFinder
{
    private MemoryCache $cache;

    public function __construct(
        private PropertyStoreMapper $propertyStoreMapper,
        private StorageManager      $storageManager,
    )
    {
        $this->cache = new MemoryCache();
    }

    /** @return Store[] */
    public function find(MetaData $metaData): array
    {
        return $this->cache->get($metaData->className, fn() => $this->gather($metaData));
    }

    /** @return Store[] */
    private function gather(MetaData $metaData): array
    {
        $stores = [];

        foreach ($this->propertyStoreMapper->get($metaData)->stores() as $storeName) {
            $stores[] = $this->storageManager->controller($metaData->entity->storage)->store($storeName);
        }

        return $stores;
    }
}
