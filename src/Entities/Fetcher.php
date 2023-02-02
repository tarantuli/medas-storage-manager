<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\EntityManager\Entities\{Fetcher as FetcherInterface, FetchResult, KeyMaker};
use Medas\EntityManager\Hydration\ValueGetter;
use Medas\EntityManager\MetaData;
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Selector\Selector;
use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\Entities\Exceptions\StoreDoesNotHavePropertyException;
use Medas\StorageManagerTest\MockUps\Migrations\StoredEntity;
use Medas\StorageManager\Interfaces\{Store, StoreRecord};

#[Service]
class Fetcher implements FetcherInterface
{
    /** @var StoreRecord[] */
    private array $records = [];

    public function __construct(
        private readonly DataSerializer  $dataSerializer,
        private readonly KeyMaker        $keyMaker,
        private readonly MetaDataManager $metaDataManager,
        private readonly ValueGetter     $entityValueGetter,
    )
    {
    }

    public function fetchValue(MetaData $metaData, object $entity, MetaData\Property $property): FetchResult
    {
        $record = $this->getRecord($metaData, $entity);

        if ($record === null) {
            return new FetchResult(false);
        }

        if (isset($record[$property->name])) {
            return new FetchResult(true, $record[$property->name]);
        }

        throw new StoreDoesNotHavePropertyException($this->getStore($metaData), $property->name);
    }

    private function getRecord(MetaData $metaData, object $entity): ?StoreRecord
    {
        $idValues = $this->entityValueGetter->get($entity, $metaData->idProperties);
        $this->dataSerializer->serialize($metaData, $idValues);
        $key = $this->keyMaker->get($entity::class, $idValues);

        if (!array_key_exists($key, $this->records)) {
            $record = $this->getStore($metaData)->fetchRecord($idValues);
            $this->addToCache($metaData, $record, $key);
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

        $this->dataSerializer->deserialize($metaData, $record);

        if ($key === null) {
            $key = $this->getKeyFromRecord($metaData, $record->data());
        }

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

    private function getStore(MetaData $metaData): Store
    {
        return storage($metaData->entity->storage)->store($metaData->entity->store);
    }

    public function fetch(Selector $selector = null, array $arguments = []): array
    {
        $metaData = $this->metaDataManager->get(StoredEntity::class);
        $query = storage($metaData->entity->storage)->controller()->actionBuilder()
            ->fromSelector($selector, $arguments);

        $query->execute();
        $records = $query->recordSet()->fetchRecords();

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
