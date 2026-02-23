<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\Core\{
    Attributes\Service,
    Interfaces\Collection,
    Interfaces\IsLazyLoaded,
    Interfaces\SettableCollection,
    Interfaces\TracksChanges,
    Types\Collection as CollectionType,
    Types\Relation
};
use Medas\EntityManager\Entities\ValueFetchers\FetchResult;
use Medas\EntityManager\MetaData;
use Medas\StorageManager\{Exceptions\UnhandledRawItemType, StorageManager};

#[Service]
readonly class CollectionFetcher
{
    public function __construct(
        private StorageManager $storageManager,
        private StoresFinder   $storesFinder,
    )
    {
    }

    public function fetch(MetaData\Property $property, MetaData $metaData, object $entity): FetchResult
    {
        /** @var CollectionType $propertyType */
        $propertyType = $property->type;

        /** @var Collection $collection */
        $collection = new $propertyType->collectionType();

        if ($collection instanceof IsLazyLoaded) {
            $collection->setLoader(fn() => $this->fetchCollectionItems($metaData, $entity, $property));
        }
        elseif ($collection instanceof SettableCollection) {
            $collection->setData($this->fetchCollectionItems($metaData, $entity, $property));
        }
        else {
            foreach ($this->fetchCollectionItems($metaData, $entity, $property) as $item) {
                $collection[] = $item;
            }
        }

        if ($collection instanceof TracksChanges) {
            $collection->resetChangeTracking();
        }

        return new FetchResult(true, $collection);
    }

    private function fetchCollectionItems(MetaData $metaData, object $entity, MetaData\Property $property): array
    {
        /** @var CollectionType $propertyType */
        $propertyType = $property->type;
        $rawItemType = $propertyType->contentType;

        if (class_exists($rawItemType)) {
            $itemType = new Relation($rawItemType);
        }
        else {
            throw new UnhandledRawItemType($rawItemType);
        }

        $serializer = $this->storageManager->controller($metaData->entity->storage)->serializer();
        $items = [];

        foreach ($this->storesFinder->find($metaData) as $store) {
            $fetcher
                = $this->storageManager->controller($store->storage())->recordFetchers()->collectionRecordFetcher();

            $records = $fetcher->fetch($store, $entity, $property);

            foreach ($records as $record) {
                $items[] = $serializer->unserialize($record['value'], $itemType);
            }
        }

        return $items;
    }
}
