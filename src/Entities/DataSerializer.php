<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\Core\{
    Attributes\Service,
    Interfaces\PropertyHandler,
    Interfaces\Serializer,
    Interfaces\ServiceManager,
    Interfaces\Type,
    Types\FloatingPoint,
    Types\Integer,
    Types\Relation
};
use Medas\EntityManager\{Exceptions\PropertyDoesNotExist, MetaData, MetaDataManager};
use Medas\StorageManager\{Interfaces\Record, StorageManager};

#[Service]
readonly class DataSerializer
{
    public function __construct(
        private MetaDataManager $metaDataManager,
        private ServiceManager  $serviceManager,
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
            $handler = $this->serviceManager->resolve($class);
            $value = $handler->unserialize($value);
        }

        return $value;
    }

    /**
     * A derived value as the storage returned it, in the PHP type its declaration names.
     * For a property, the hydrator casts what the serializer leaves as stored (a number
     * the driver hands over as text, an enum's backing value); a derived value is never
     * hydrated, so it is cast here.
     */
    public function unserializeDerivedValue(MetaData $metaData, mixed $value, Type $type): mixed
    {
        $value = $this->getStorageSerializer($metaData)->unserialize($value, $type);

        return match (true) {
            $value === null => null,
            $type instanceof Integer => (int) $value,
            $type instanceof FloatingPoint => (float) $value,
            $type instanceof Relation && enum_exists($type->entity) => $this->enumCase($type->entity, $value),
            default => $value,
        };
    }

    /** @param class-string<\BackedEnum> $enum */
    private function enumCase(string $enum, mixed $value): \BackedEnum
    {
        $isIntBacked = new \ReflectionEnum($enum)->getBackingType()?->getName() === 'int';

        return $enum::from($isIntBacked ? (int) $value : (string) $value);
    }

    public function serializeArray(MetaData $metaData, array &$data): void
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
            $propertyHandler = $this->serviceManager->resolve($class);
            $value = $propertyHandler->serialize($value);
        }

        return $this->getStorageSerializer($metaData)->serialize($value);
    }

    private function getStorageSerializer(MetaData $metaData): Serializer
    {
        return $this->storageManager->controller($metaData->entity->storage)->serializer();
    }
}
