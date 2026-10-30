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
        // Keyed by the storage object as well: a storage can be replaced under the same name
        // (StorageManager::add()), and its stores must then belong to the new one. The cached
        // stores keep the old storage referenced, so its object id is not reused meanwhile.
        $storage = $this->storageManager->byName($metaData->entity->storage);
        $key = $metaData->className . '@' . spl_object_id($storage);

        return $this->cache->get($key, fn() => $this->gather($metaData));
    }

    /** @return Store[] */
    private function gather(MetaData $metaData): array
    {
        $stores = [];

        foreach ($this->propertyStoreMapper->get($metaData)->stores() as $storeName) {
            $stores[] = $this->storageManager->store($storeName, $metaData->entity->storage);
        }

        return $stores;
    }
}
