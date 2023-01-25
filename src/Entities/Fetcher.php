<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\EntityManager\Entities\{Fetcher as FetcherInterface, FetchResult, KeyMaker};
use Medas\EntityManager\Hydration\ValueGetter;
use Medas\EntityManager\MetaData;
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Selector\Selector;
use Medas\EntityManager\Types\Relation;
use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\Entities\Exceptions\StoreDoesNotHavePropertyException;
use Medas\StorageManager\Interfaces\{Store, StoreRecord};

#[Service]
class Fetcher implements FetcherInterface
{
    /** @var StoreRecord[] */
    private array $records = [];

    public function __construct(
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

        $this->deserialize($metaData, $record);

        if ($key === null) {
            $key = $this->getKeyFromRecord($metaData, $record->data());
        }

        $this->records[$key] = $record;

        return $record;
    }

    private function deserialize(MetaData $metaData, StoreRecord &$record): void
    {
        $serializer = storage($metaData->entity->storage)->controller()->serializer();

        foreach ($record as $key => $value) {
            $type = $metaData->property($key)->type;

            if ($type instanceof Relation) {
                // Deserialize using the type of the referenced ID property of the related class
                $type = $this->metaDataManager->get($type->entity)->idProperty->type;
            }

            $record[$key] = $serializer->deserialize($type, $value);
        }
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
        $metaData = $this->metaDataManager->get($selector->entity());
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
