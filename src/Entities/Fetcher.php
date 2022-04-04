<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\EntityManager\Entities\Fetcher as FetcherInterface;
use Medas\EntityManager\Entities\FetchResult;
use Medas\EntityManager\Entities\KeyMaker;
use Medas\EntityManager\Hydration\ValueGetter;
use Medas\EntityManager\MetaData;
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Selector\Selector;
use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\Databases\Pdo\Queries\SelectQueryBuilder;
use Medas\StorageManager\Entities\Exceptions\StoreDoesNotHavePropertyException;
use Medas\StorageManager\Interfaces\Store;
use Medas\StorageManager\Interfaces\StoreRecord;

#[Service]
class Fetcher implements FetcherInterface
{
    /** @var StoreRecord[] */
    private array $records = [];

    public function __construct(
        private KeyMaker           $keyMaker,
        private MetaDataManager    $metaDataManager,
        private ValueGetter        $entityValueGetter,
        private SelectQueryBuilder $selectQueryBuilder,
    )
    {
    }

    public function fetchValue(MetaData $metaData, object $entity, MetaData\Property $property): FetchResult
    {
        $record = $this->getRecord($metaData, $entity);

        if ($record === null) {
            return new FetchResult(false);
        }

        try {
            return new FetchResult(true, $record[$property->name]);
        }
        catch (\Exception) {
            throw new StoreDoesNotHavePropertyException($this->getStore($metaData), $property->name);
        }
    }

    private function getRecord(MetaData $metaData, object $entity): ?StoreRecord
    {
        $idValues = $this->entityValueGetter->get($entity, $metaData->idProperties);
        $key = $this->keyMaker->get($entity::class, $idValues);

        if (!isset($this->records[$key])) {
            $this->addToCache($metaData, $this->getStore($metaData)->fetchRecord($idValues), $key);
        }

        return $this->records[$key];
    }

    private function addToCache(MetaData $metaData, StoreRecord|null $record, string|null $key = null): StoreRecord|null
    {
        if ($record === null) {
            if ($key !== null) {
                $this->records[$key] = null;
            }

            return null;
        }

        if ($key === null) {
            $key = $this->getKeyFromRecord($metaData, $record->data());
        }

        $this->deserialize($metaData, $record);
        $this->records[$key] = $record;

        return $record;
    }

    private function getKeyFromRecord(MetaData $metaData, array $data): string
    {
        $idValues = [];
        foreach ($metaData->idProperties as $idProperty) {
            $idValues[$idProperty->name] = $data[$idProperty->name];
        }
        return $this->keyMaker->get($metaData->className, $idValues);
    }

    private function deserialize(MetaData $metaData, StoreRecord &$record): void
    {
        $serializerFinder = storage($metaData->entity->storage)->getTypeSerializerFinder();
        foreach ($record as $key => &$value) {
            $serializer = $serializerFinder->for($metaData->property($key)->type);
            $value = $serializer->deserialize($value);
        }
    }

    private function getStore(MetaData $metaData): Store
    {
        return storage($metaData->entity->storage)->store($metaData->entity->store);
    }

    public function fetchRecord(Selector $selector, array $arguments = []): array|null
    {
        $query = $this->selectQueryBuilder->build($selector, $arguments);
        $query->execute();
        $record = $query->storage()->fetchRecord();

        return $record ? $this->addToCache($this->metaDataManager->get($selector->get()->entity), $record)->data() : null;
    }

    public function fetch(Selector $selector = null, array $arguments = []): array
    {
        $query = $this->selectQueryBuilder->build($selector, $arguments);
        $query->execute();
        $records = $query->storage()->fetchRecords();
        $metaData = $this->metaDataManager->get($selector->get()->entity);

        foreach ($records as &$record) {
            $record = $this->addToCache($metaData, $record);
        }

        return $records;
    }

    public function updateRecord(MetaData $metaData, array $values, array $idValues)
    {
        $key = $this->getKeyFromRecord($metaData, $idValues);

        if (isset($this->records[$key])) {
            $this->records[$key]->patch($values);
        }
    }

    public function removeRecord(MetaData $metaData, array $idValues)
    {
        $key = $this->getKeyFromRecord($metaData, $idValues);

        unset($this->records[$key]);
    }
}
