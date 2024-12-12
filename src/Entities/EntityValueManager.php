<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\Core\Attributes\{EventListener, Service};
use Medas\EntityManager\Entities\{
    KeyMaker,
    ValueFetchers\EntityValueFetcher,
    ValueFetchers\FetchResult
};
use Medas\EntityManager\Events\MustClearEntityValueCaches;
use Medas\EntityManager\Hydration\ValueGetter;
use Medas\EntityManager\MetaData;
use Medas\EntityManager\Types\Collection as CollectionType;
use Medas\StorageManager\Interfaces\Record;

#[Service]
readonly class EntityValueManager implements EntityValueFetcher
{
    private RecordCollection $entityRecords;

    public function __construct(
        private CollectionFetcher  $collectionFetcher,
        private DataSerializer     $dataSerializer,
        private KeyMaker           $keyMaker,
        private StoreRecordManager $storeRecordManager,
        private StoresFinder       $storesFinder,
        private ValueGetter        $entityValueGetter,
    )
    {
        $this->entityRecords = new RecordCollection();
    }

    public function fetch(MetaData $metaData, object $entity, MetaData\Property $property): FetchResult
    {
        if ($property->type instanceof CollectionType) {
            return $this->collectionFetcher->fetch($property, $metaData, $entity);
        }

        $record = $this->getEntityRecord($metaData, $entity);

        if ($record === null) {
            return new FetchResult(false);
        }

        if (isset($record[$property->name])) {
            return new FetchResult(true, $record[$property->name]);
        }

        throw new Exceptions\StoresDontHaveProperty(
            $this->storesFinder->find($metaData),
            $property->name
        );
    }

    private function getEntityRecord(MetaData $metaData, object $entity): Record|null
    {
        $idValue = $this->entityValueGetter->getValue($entity, $metaData->idProperty);
        $key = $this->keyMaker->get($entity::class, $idValue);

        if (!$this->entityRecords->offsetExists($key)) {
            $entityRecord = null;

            foreach ($this->storesFinder->find($metaData) as $store) {
                $storeRecord = $this->storeRecordManager->fetchOne($metaData, $store, $idValue);

                if ($storeRecord) {
                    if ($entityRecord === null) {
                        $entityRecord = $storeRecord;
                    }
                    else {
                        $entityRecord->patch($storeRecord->data());
                    }
                }
            }

            $this->entityRecords->offsetSet($key, $entityRecord);
        }

        return $this->entityRecords[$key];
    }

    #[EventListener]
    public function clearCaches(
        /** @noinspection PhpUnusedParameterInspection */
        MustClearEntityValueCaches $event
    ): void
    {
        $this->entityRecords->clear();
    }
}
