<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Entities\{IdValue, KeyMaker, SelectorRecordsFetcher};
use Medas\EntityManager\MetaData;
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Selector\Selector;
use Medas\StorageManager\Interfaces\{Record, Store};
use Medas\StorageManager\StorageManager;

#[Service]
readonly class StoreRecordManager implements SelectorRecordsFetcher
{
    private RecordSet $storeRecords;

    public function __construct(
        private DataSerializer  $dataSerializer,
        private IdValue         $idValue,
        private KeyMaker        $keyMaker,
        private MetaDataManager $metaDataManager,
        private StorageManager  $storageManager,
    )
    {
        $this->storeRecords = new RecordSet();
    }

    public function fetch(Selector $selector = null, array $arguments = []): array
    {
        $entity = $selector->entity();
        $metaData = $this->metaDataManager->get($entity);
        $actionSet = $this->storageManager->controller($metaData->entity->storage)->actionBuilders()
            ->selectorAction()->build($selector, $arguments);

        $this->storageManager->controller($metaData->entity->storage)->actionExecutor()->executeSet($actionSet);

        $records = $actionSet->recordSet()->fetchRecords();

        foreach ($records as &$record) {
            $idValue = $this->idValue->get($record, $metaData);
            $record = $this->unserializeAndCache($metaData, $metaData->entity->store, $idValue, $record);
        }

        return $records;
    }

    public function fetchOne(MetaData $metaData, Store $store, mixed $idValue): Record|null
    {
        $key = $this->keyMaker->get($store->name(), $idValue);

        if ($this->storeRecords->offsetExists($key)) {
            return $this->storeRecords[$key];
        }

        $controller = $this->storageManager->controller($store->storage());

        $actionSet = $controller->actionBuilders()
            ->get()->build([$store], [$metaData->idProperty->name => $idValue]);

        $controller->actionExecutor()->executeSet($actionSet);

        $record = $actionSet->recordSet()->fetchRecord();

        return $this->unserializeAndCache($metaData, $store->name(), $idValue, $record);
    }

    private function unserializeAndCache(MetaData $metaData, string $storeName, mixed $idValue, Record|null $record): Record|null
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

        if (isset($this->storeRecords->records[$key])) {
            $this->storeRecords->records[$key]->patch($values);
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
