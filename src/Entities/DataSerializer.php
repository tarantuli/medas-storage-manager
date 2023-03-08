<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\EntityManager\{MetaData, MetaDataManager, Types\Relation};
use Medas\ServiceManager\Attributes\Service;
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
        $serializer = storage($metaData->entity->storage)->controller()->serializer();

        foreach ($data as $key => $value) {
            $type = $metaData->property($key)->type;

            if ($type instanceof Relation && !enum_exists($type->entity)) {
                // Unserialize using the type of the referenced ID property of the related class
                $type = $this->metaDataManager->get($type->entity)->idProperty->type;
            }

            $data[$key] = $serializer->unserialize($type, $value);
        }
    }

    public function serialize(MetaData $metaData, iterable &$data): void
    {
        $serializer = storage($metaData->entity->storage)->controller()->serializer();

        foreach ($data as $key => $value) {
            $data[$key] = $serializer->serialize($value);
        }
    }
}
