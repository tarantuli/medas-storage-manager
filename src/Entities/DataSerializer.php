<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\EntityManager\{MetaData, MetaDataManager, Types\Relation};
use Medas\ServiceManager\Attributes\Service;
use Medas\ServiceManager\Interfaces\Serializer;
use Medas\StorageManager\Interfaces\StoreRecord;

#[Service]
class DataSerializer
{
    public function __construct(
        private readonly MetaDataManager $metaDataManager,
    )
    {
    }

    public function unserialize(MetaData $metaData, StoreRecord &$data): void
    {
        $serializer = $this->getSerializer($metaData);

        foreach ($data as $key => $value) {
            $type = $metaData->property($key)->type;

            if ($type instanceof Relation && !enum_exists($type->entity)) {
                // Unserialize using the type of the referenced ID property of the related class
                $type = $this->metaDataManager->get($type->entity)->idProperty->type;
            }

            $data[$key] = $serializer->unserialize($value, $type);
        }
    }

    public function serializeArray(MetaData $metaData, iterable &$data): void
    {
        $serializer = $this->getSerializer($metaData);

        foreach ($data as $key => $value) {
            $data[$key] = $serializer->serialize($value);
        }
    }

    public function serializeDatum(MetaData $metaData, mixed $datum): mixed
    {
        return $this->getSerializer($metaData)->serialize($datum);
    }

    private function getSerializer(MetaData $metaData): Serializer
    {
        return storage($metaData->entity->storage)->controller()->serializer();
    }
}
