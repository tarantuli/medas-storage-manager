<?php

declare(strict_types=1);

namespace Medas\StorageManager\Shared;

use Medas\Core\{
    Attributes\Service,
    Interfaces\HasId,
    Interfaces\Serializer,
    Interfaces\Type,
    Interfaces\Uuid,
    Interfaces\UuidProvider,
    Types\Boolean,
    Types\Relation,
    Types\Uuid as UuidType
};

#[Service]
class ValueSerializer implements Serializer
{
    public function serialize(mixed $value): mixed
    {
        // Get the ID first, so other serializers can process its value
        if ($value instanceof HasId) {
            $value = $value->id();
        }

        if ($value instanceof Uuid) {
            return $value->toBytes();
        }

        if ($value instanceof \DateTime) {
            $value->setTimezone(new \DateTimeZone(date_default_timezone_get()));

            return $value->format('Y-m-d H:i:s');
        }

        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        if (is_bool($value)) {
            return (string) (int) $value;
        }

        return $value;
    }

    public function unserialize(mixed $value, Type $type = null): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($type instanceof UuidType) {
            return service(UuidProvider::class)->fromBytes($value);
        }

        if ($type instanceof Boolean) {
            return (bool) $value;
        }

        if ($type instanceof Relation) {
            if (enum_exists($type->entity)) {
                return $value;
            }

            return em()->get($type->entity, $value);
        }

        return $value;
    }
}
