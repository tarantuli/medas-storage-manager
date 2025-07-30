<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\Core\{
    Attributes\Service,
    Interfaces\PropertyHandler,
    Interfaces\Serializer,
    Types\Relation
};
use Medas\EntityManager\{Exceptions\PropertyDoesNotExist, MetaData, MetaDataManager};
use Medas\StorageManager\{Interfaces\Record, StorageManager};

#[Service]
readonly class DataSerializer
{
    public function __construct(
        private MetaDataManager $metaDataManager,
        private StorageManager  $storageManager,
    )
    {
    }

    public function unserializeArray(MetaData $metaData, Record &$data): void
    {
        $serializer = $this->getStorageSerializer($metaData);

        foreach ($data as $key => $value) {
            try {
                $data[$key] = $this->unserializeValue(
                    $metaData,
                    $metaData->property($key),
                    $value,
                    $serializer
                );
            }
            catch (PropertyDoesNotExist) {
                continue;
            }
        }
    }

    public function unserializeValue(
        MetaData          $metaData,
        MetaData\Property $property,
        mixed             $value,
        Serializer|null   $serializer = null
    ): mixed
    {
        $type = $property->type;
        $serializer ??= $this->getStorageSerializer($metaData);

        if ($type instanceof Relation && !enum_exists($type->entity)) {
            // Unserialize using the type of the referenced ID property of the related class
            $type = $this->metaDataManager->get($type->entity)->idProperty->type;
        }

        $value = $serializer->unserialize($value, $type);

        if ($class = $property->handler) {
            // This property has been assigned a handler, let it unserialize afterward
            /** @var PropertyHandler $handler */
            $handler = service($class);
            $value = $handler->unserialize($value);
        }

        return $value;
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
            /** @var PropertyHandler $propertyHandler */
            $propertyHandler = service($class);
            $value = $propertyHandler->serialize($value);
        }

        return $this->getStorageSerializer($metaData)->serialize($value);
    }

    private function getStorageSerializer(MetaData $metaData): Serializer
    {
        return $this->storageManager->controller($metaData->entity->storage)->serializer();
    }
}
