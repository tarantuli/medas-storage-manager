<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\CacheManager;
use Medas\EntityManager\MetaData;
use Medas\StorageManager\Interfaces\Store;
use Medas\StorageManager\Structure\EntityStructureFinder;

#[Service]
class StoresFinder
{
    public function __construct(
        private readonly CacheManager          $cacheManager,
        private readonly EntityStructureFinder $entityStructureFinder,
    )
    {
    }

    /** @return Store[] */
    public function find(MetaData $metaData): array
    {
        return $this->cacheManager->get()->get(
            [self::class, $metaData->className],
            fn() => $this->gather($metaData)
        );
    }

    /** @return Store[] */
    private function gather(MetaData $metaData): array
    {
        $blueprint = $this->entityStructureFinder->find($metaData->className);
        $storeNames = [];
        foreach ($blueprint->fields() as $field) {
            $storeNames[] = $field->store;
        }
        $stores = [];

        foreach (array_unique($storeNames) as $name) {
            $stores[] = storage($metaData->entity->storage)->store($name);
        }

        return $stores;
    }
}
