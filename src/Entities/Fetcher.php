<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\EntityManager\Entities\Fetcher as FechterInterface;
use Medas\EntityManager\Hydration\ValueGetter;
use Medas\EntityManager\MetaData;
use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\Interfaces\Store;
use Medas\StorageManager\Interfaces\StoreRecord;

#[Service]
class Fetcher implements FechterInterface
{
    private \SplObjectStorage $records;

    public function __construct(
        private ValueGetter $valueGetter,
    )
    {
        $this->records = new \SplObjectStorage();
    }

    public function fetch(MetaData $metaData, object $entity, MetaData\Property $property): mixed
    {
        $record = $this->getRecord($metaData, $entity);

        return $record->get($property->name);
    }

    private function getRecord(MetaData $metaData, object $entity): StoreRecord
    {
        if (!isset($this->records[$entity])) {
            $this->records[$entity] = $this->getStore($metaData)->fetchRecord($this->valueGetter->get($entity, $metaData->idProperties));
        }

        return $this->records[$entity];
    }

    private function getStore(MetaData $metaData): Store
    {
        return storage($metaData->entity->storage)->store($metaData->entity->store);
    }

    public function fetchRecord(MetaData $metaData, array $conditions): array
    {
        return $this->getStore($metaData)->fetchRecord($conditions)->data();
    }

    public function fetchAll(MetaData $metaData, array $conditions): array
    {
        return $this->getStore($metaData)->fetchAll($conditions);
    }
}
