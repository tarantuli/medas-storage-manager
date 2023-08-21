<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Entities\{Fetcher, FetchResult, IdValue, KeyMaker};
use Medas\EntityManager\Hydration\ValueGetter;
use Medas\EntityManager\MetaData;
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Selector\Selector;
use Medas\EntityManager\Types\{Collection as CollectionType};
use Medas\StorageManager\Entities\Exceptions\StoresDontHaveProperty;
use Medas\StorageManager\Interfaces\{StoreRecord};
use Medas\StorageManager\StorageManager;

#[Service]
class RecordManager implements Fetcher
{
    /** @var StoreRecord[] */
    private array $records = [];

    public function __construct(
        private readonly CollectionFetcher $collectionFetcher,
        private readonly DataSerializer    $dataSerializer,
        private readonly IdValue           $idValue,
        private readonly KeyMaker          $keyMaker,
        private readonly MetaDataManager   $metaDataManager,
        private readonly StorageManager    $storageManager,
        private readonly StoresFinder      $storesFinder,
        private readonly ValueGetter       $entityValueGetter,
    )
    {
    }

    public function fetchValue(MetaData $metaData, object $entity, MetaData\Property $property): FetchResult
    {
        if ($property->type instanceof CollectionType) {
            return $this->collectionFetcher->fetch($property, $metaData, $entity);
        }

        $record = $this->getRecord($metaData, $entity);

        if ($record === null) {
            return new FetchResult(false);
        }

        if (isset($record[$property->name])) {
            return new FetchResult(true, $record[$property->name]);
        }

        throw new StoresDontHaveProperty($this->storesFinder->find($metaData), $property->name);
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
            $record = null;

            foreach ($this->storesFinder->find($metaData) as $store) {
                if ($newRecord = $store->fetchRecord([$metaData->idProperty->name => $idValue])) {
                    if ($record === null) {
                        $record = $newRecord;
                    }
                    else {
                        $record->patch($newRecord->data());
                    }
                }
            }

            $this->unserializeAndCache($metaData, $record, $key);
        }

        return $this->records[$key];
    }

    private function unserializeAndCache(MetaData $metaData, StoreRecord|null $record, string $key): StoreRecord|null
    {
        if ($record === null) {
            $this->records[$key] = null;
            return null;
        }

        $this->dataSerializer->unserializeArray($metaData, $record);

        $this->records[$key] = $record;

        return $record;
    }

    public function fetch(Selector $selector = null, array $arguments = []): array
    {
        $entity = $selector->definition()->entity;
        $metaData = $this->metaDataManager->get($entity);
        $query = $this->storageManager->controller($metaData->entity->storage)->actionBuilder()
            ->fromSelector($selector, $arguments);

        $query->execute();
        $records = $query->recordSet()->fetchRecords();

        foreach ($records as &$record) {
            $key = $this->keyMaker->get($entity, $this->idValue->get($record, $metaData));
            $record = $this->unserializeAndCache($metaData, $record, $key);
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

    private function getKeyFromRecord(MetaData $metaData, array $data): string
    {
        return $this->keyMaker->get($metaData->className, $data[$metaData->idProperty->name]);
    }
}
