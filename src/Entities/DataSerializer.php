<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\Serializer;
use Medas\EntityManager\{Exceptions\PropertyDoesNotExist,
    MetaData,
    MetaDataManager,
    Properties\Handler,
    Types\Relation};
use Medas\StorageManager\Interfaces\StoreRecord;
use Medas\StorageManager\StorageManager;

#[Service]
class DataSerializer
{
    public function __construct(
        private readonly MetaDataManager $metaDataManager,
        private readonly StorageManager  $storageManager,
    )
    {
    }

    public function unserializeArray(MetaData $metaData, StoreRecord &$data): void
    {
        foreach ($data as $key => $value) {
            try {
                $data[$key] = $this->unserializeValue($metaData, $metaData->property($key), $value);
            }
            catch (PropertyDoesNotExist) {
                continue;
            }
        }
    }

    public function unserializeValue(MetaData $metaData, MetaData\Property $property, mixed $value): mixed
    {
        $type = $property->type;

        if ($type instanceof Relation && !enum_exists($type->entity)) {
            // Unserialize using the type of the referenced ID property of the related class
            $type = $this->metaDataManager->get($type->entity)->idProperty->type;
        }

        $value = $this->getStorageSerializer($metaData)->unserialize($value, $type);

        if ($class = $property->handler) {
            // This property has been assigned a handler, let it unserialize afterwards
            /** @var Handler $handler */
            $handler = service($class);
            $value = $handler->unserialize($value);
        }

        return $value;
    }

    private function getStorageSerializer(MetaData $metaData): Serializer
    {
        return $this->storageManager->controller($metaData->entity->storage)->serializer();
    }

    public function serializeArray(MetaData $metaData, iterable &$data): void
    {
        foreach ($data as $key => $value) {
            $data[$key] = $this->serializeValue($metaData, $metaData->property($key), $value);
        }
    }

    public function serializeValue(MetaData $metaData, MetaData\Property $property, mixed $value): mixed
    {
        if ($class = $property->handler) {
            // This property has been assigned a handler, let it serialize first
            /** @var Handler $propertyHandler */
            $propertyHandler = service($class);
            $value = $propertyHandler->serialize($value);
        }

        return $this->getStorageSerializer($metaData)->serialize($value);
    }
}
