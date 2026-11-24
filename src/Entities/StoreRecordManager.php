<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\Core\Attributes\{EventListener, Service};
use Medas\EntityManager\Entities\{
    IdValue,
    KeyMaker,
    ValueFetchers\FetchResult,
    ValueFetchers\SelectorRecordsFetcher
};
use Medas\EntityManager\Events\MustClearEntityValueCaches;
use Medas\EntityManager\Exceptions\DerivedValueNameIsAProperty;
use Medas\EntityManager\MetaData;
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Selector\{DerivedValues\DerivedValue, Selector};
use Medas\StorageManager\{Interfaces\Record, Interfaces\Store, SelectorFetcher, StorageManager};

#[Service]
readonly class StoreRecordManager implements SelectorRecordsFetcher
{
    private RecordCollection $storeRecords;

    public function __construct(
        private DataSerializer  $dataSerializer,
        private IdValue         $idValue,
        private KeyMaker        $keyMaker,
        private MetaDataManager $metaDataManager,
        private SelectorFetcher $selectorfetcher,
        private StorageManager  $storageManager,
    )
    {
        $this->storeRecords = new RecordCollection();
    }

    public function fetch(Selector $selector, array $arguments = []): FetchResult
    {
        $metaData = $this->metaDataManager->get($selector->entity());
        $derivedValues = $selector->definition()->derivedValues;

        foreach ($derivedValues as $derivedValue) {
            if ($metaData->property($derivedValue->name, ignoreUnknownProperties: true) !== null) {
                throw new DerivedValueNameIsAProperty($metaData->className, $derivedValue->name);
            }
        }

        $records = $this->selectorfetcher->fetch($selector, $arguments);
        $derived = [];

        foreach ($records as $i => &$record) {
            // Taken out before the record is cached: it is the entity's, the derived values are this query's
            $derived[$i] = $this->takeDerivedValues($metaData, $derivedValues, $record);
            $idValue = $this->idValue->get($record, $metaData);

            $record = $this->unserializeAndCache(
                $metaData,
                $metaData->entity->store,
                $idValue,
                $record
            );
        }

        return new FetchResult(true, $records, $derived);
    }

    /**
     * @param DerivedValue[] $derivedValues
     *
     * @return array<string, mixed>
     */
    private function takeDerivedValues(MetaData $metaData, array $derivedValues, Record $record): array
    {
        $values = [];

        foreach ($derivedValues as $derivedValue) {
            $values[$derivedValue->name] = $this->dataSerializer->unserializeDerivedValue(
                $metaData,
                $record[$derivedValue->name],
                $derivedValue->type
            );

            unset($record[$derivedValue->name]);
        }

        return $values;
    }

    public function fetchCount(Selector $selector, array $arguments = []): FetchResult
    {
        $entity = $selector->entity();
        $metaData = $this->metaDataManager->get($entity);
        $actionSet = $this->storageManager->controller($metaData->entity->storage)->actionBuilders()
            ->selectorAction()->build($selector, $arguments, true);

        $this->storageManager->controller($metaData->entity->storage)->actionExecutor()->executeSet($actionSet);

        return new FetchResult(true, $actionSet->lastRecordSet->fetchRecord()['count']);
    }

    #[EventListener]
    public function clearCaches(
        /** @noinspection PhpUnusedParameterInspection */
        MustClearEntityValueCaches $event,
    ): void
    {
        $this->storeRecords->clear();
    }

    public function fetchOne(MetaData $metaData, Store $store, mixed $idValue): Record|null
    {
        $key = $this->keyMaker->get($store->name(), $idValue);

        if ($this->storeRecords->offsetExists($key)) {
            return $this->storeRecords[$key];
        }

        $controller = $this->storageManager->controller($store->storage());

        // No ownership filters here: they belong where user input is filtered (the REST
        // controllers and their helpers). A fetch by id has no such context; it also
        // serves internal code, which must not depend on who happens to be signed in.
        $arguments = [$metaData->idProperty->name => $idValue];
        $actionSet = $controller->actionBuilders()
            ->get()->build([$store], $arguments);

        $controller->actionExecutor()->executeSet($actionSet);

        $record = $actionSet->lastRecordSet->fetchRecord();

        return $this->unserializeAndCache($metaData, $store->name(), $idValue, $record);
    }

    private function unserializeAndCache(
        MetaData    $metaData,
        string      $storeName,
        mixed       $idValue,
        Record|null $record
    ): Record|null
    {
        $key = $this->keyMaker->get($storeName, $idValue);

        if ($record === null) {
            $this->storeRecords->offsetSet($key, null);

            return null;
        }

        $this->dataSerializer->unserializeArray($metaData, $record);
        $this->storeRecords->offsetSet($key, $record);

        return $record;
    }

    public function updateRecord(MetaData $metaData, array $values, array $idValues): void
    {
        $key = $this->getKeyFromRecord($metaData, $idValues);

        if ($this->storeRecords->hasKey($key)) {
            $this->storeRecords[$key]->patch($values);
        }
    }

    public function removeRecord(MetaData $metaData, array $idValues): void
    {
        $key = $this->getKeyFromRecord($metaData, $idValues);

        $this->storeRecords->offsetUnset($key);
    }

    private function getKeyFromRecord(MetaData $metaData, array $data): string
    {
        return $this->keyMaker->get($metaData->className, $data[$metaData->idProperty->name]);
    }
}
