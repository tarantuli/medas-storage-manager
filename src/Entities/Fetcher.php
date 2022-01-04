<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\EntityManager\Entities\Fetcher as FechterInterface;
use Medas\EntityManager\MetaData;
use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\Interfaces\Store;
use Medas\StorageManager\Interfaces\StoreRecord;

#[Service]
class Fetcher implements FechterInterface
{

    public function fetchRecord(MetaData $metaData, array $filters): ?StoreRecord
    {
        return $this->getStore($metaData)->fetchRecord($filters);
    }

    private function getStore(MetaData $metaData): Store
    {
        return storage($metaData->entity->storage)->store($metaData->entity->store);
    }
}
