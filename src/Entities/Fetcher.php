<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\TracksChanges;
use Medas\EntityManager\Entities\{Fetcher as FetcherInterface, FetchResult, IdValue, KeyMaker};
use Medas\EntityManager\Hydration\ValueGetter;
use Medas\EntityManager\MetaData;
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Selector\Selector;
use Medas\EntityManager\Types\{Collection, Relation};
use Medas\StorageManager\Entities\Exceptions\StoreDoesNotHaveProperty;
use Medas\StorageManager\Interfaces\{Store, StoreRecord};

#[Service]
class Fetcher implements FetcherInterface
{
    /** @var StoreRecord[] */
    private array $records = [];

    public function __construct(
        private readonly DataSerializer  $dataSerializer,
        private readonly IdValue         $idValue,
        private readonly KeyMaker        $keyMaker,
        private readonly MetaDataManager $metaDataManager,
        private readonly ValueGetter     $entityValueGetter,
    )
    {
    }

    private function fetchCollectionItems(MetaData $metaData, object $entity, MetaData\Property $property): array
    {
        /** @var Collection $propertyType */
        $propertyType = $property->type;
        $rawItemType = $propertyType->contentType;

        if (class_exists($rawItemType)) {
            $itemType = new Relation($rawItemType);
        }
        else {
            throw new \Exception('unhandled raw item type ' . $rawItemType);
        }

        $serializer = storage($metaData->entity->storage)->controller()->serializer();
        $records = $this->getStore($metaData)->fetchCollectionRecord($entity, $property);
        $items = [];

        foreach ($records as $record) {
            $items[] = $serializer->unserialize($record['value'], $itemType);
        }

        return $items;
    }

    public function fetchValue(MetaData $metaData, object $entity, MetaData\Property $property): FetchResult
    {
        if ($property->type instanceof Collection) {
            /** @var TracksChanges $collection */
            $collection = new $property->type->collectionType(
                fn() => $this->fetchCollectionItems($metaData, $entity, $property)
            );

            $collection->resetChangeTracking();

            return new FetchResult(true, $collection);
        }

        $record = $this->getRecord($metaData, $entity);

        if ($record === null) {
            return new FetchResult(false);
        }

        if (isset($record[$property->name])) {
            return new FetchResult(true, $record[$property->name]);
        }

        throw new StoreDoesNotHaveProperty($this->getStore($metaData), $property->name);
    }

    private function getRecord(MetaData $metaData, object $entity): StoreRecord|null
    {
        $idValue = $this->dataSerializer->serializeValue(
            $metaData,
            $metaData->idProperty,
            $this->entityValueGetter->getValue($entity, $metaData->idProperty),
        );

        $key = $this->keyMaker->get($entity::class, $idValue);

        if (!array_key_exists($key, $this->records)) {
            $record = $this->getStore($metaData)->fetchRecord([$metaData->idProperty->name => $idValue]);
            $this->deserializeAndCache($metaData, $record, $key);
        }

        return $this->records[$key];
    }

    private function deserializeAndCache(MetaData $metaData, StoreRecord|null $record, string $key): StoreRecord|null
    {
        if ($record === null) {
            $this->records[$key] = null;
            return null;
        }

        $this->dataSerializer->unserializeArray($metaData, $record);

        $this->records[$key] = $record;

        return $record;
    }

    private function getKeyFromRecord(MetaData $metaData, array $data): string
    {
        return $this->keyMaker->get($metaData->className, $data[$metaData->idProperty->name]);
    }

    private function getStore(MetaData $metaData): Store
    {
        return storage($metaData->entity->storage)->store($metaData->entity->store);
    }

    public function fetch(Selector $selector = null, array $arguments = []): array
    {
        $entity = $selector->definition()->entity;
        $metaData = $this->metaDataManager->get($entity);
        $query = storage($metaData->entity->storage)->controller()->actionBuilder()
            ->fromSelector($selector, $arguments);

        $query->execute();
        $records = $query->recordSet()->fetchRecords();

        foreach ($records as &$record) {
            $key = $this->keyMaker->get($entity, $this->idValue->get($record, $metaData));
            $record = $this->deserializeAndCache($metaData, $record, $key);
        }

        return $records;
    }

    public function updateRecord(MetaData $metaData, array $values, array $idValues): void
    {
        $key = $this->getKeyFromRecord($metaData, $idValues);

        if (isset($this->records[$key])) {
            $this->records[$key]->patch($values);
        }
    }

    public function removeRecord(MetaData $metaData, array $idValues): void
    {
        $key = $this->getKeyFromRecord($metaData, $idValues);

        unset($this->records[$key]);
    }
}
