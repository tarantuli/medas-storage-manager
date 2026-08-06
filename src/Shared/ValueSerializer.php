<?php

declare(strict_types=1);

namespace Medas\StorageManager\Shared;

use Medas\Core\{
    Attributes\DataHolder as DataHolderAttribute,
    Attributes\EventListener,
    Attributes\Service,
    Date,
    Interfaces\HasId,
    Interfaces\Serializer,
    Interfaces\ServiceManager,
    Interfaces\Type,
    Interfaces\Uuid,
    Interfaces\UuidProvider,
    Period as PeriodInstance,
    Types\Boolean,
    Types\Date as DateType,
    Types\Period,
    Types\Relation,
    Types\Uuid as UuidType
};
use Medas\EntityManager\Events\FindEntity;
use Medas\ObjectToArraySerializer\ObjectToArraySerializer;

#[Service]
readonly class ValueSerializer implements Serializer
{
    private \DateTimeZone $dateTimeZone;

    public function __construct(
        private ObjectToArraySerializer $objectToArraySerializer,
        private ServiceManager          $serviceManager,
    )
    {
        $this->dateTimeZone = new \DateTimeZone(date_default_timezone_get());
    }

    public function serialize(mixed $value): mixed
    {
        // Get the ID first, so other serializers can process its value
        if ($value instanceof HasId) {
            $value = $value->id();
        }

        if ($value instanceof Uuid) {
            return $value->toBytes();
        }

        if ($value instanceof PeriodInstance) {
            return $value->toString();
        }

        if ($value instanceof \DateTime || $value instanceof \DateTimeImmutable) {
            $clone = clone $value;

            $clone->setTimezone($this->dateTimeZone);

            return $clone->format('Y-m-d H:i:s');
        }

        if ($value instanceof Date) {
            return sprintf('%04d-%02d-%02d', $value->year, $value->month, $value->day);
        }

        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        if (is_bool($value)) {
            return (string) (int) $value;
        }

        if (is_object($value) && attribute(DataHolderAttribute::class, new \ReflectionClass($value::class))) {
            return $this->objectToArraySerializer->serialize($value);
        }

        return $value;
    }

    #[EventListener]
    public function handleSerializeRequest(SerializeValueRequest $request): void
    {
        $request->serializedValue = $this->serialize($request->value);
    }

    public function unserialize(mixed $value, Type|null $type = null): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($type instanceof UuidType) {
            return $this->serviceManager->resolve(UuidProvider::class)->fromBytes($value);
        }

        if ($type instanceof Boolean) {
            return (bool) $value;
        }

        if ($type instanceof Period) {
            return PeriodInstance::fromString($value);
        }

        if ($type instanceof DateType) {
            [$year, $month, $day] = array_map('intval', explode('-', $value));

            return new Date($year, $month, $day);
        }

        if ($type instanceof Relation) {
            if (enum_exists($type->entity)) {
                return $value;
            }

            $event = dispatch(new FindEntity($type->entity, $value));

            return $event->entity;
        }

        return $value;
    }

    #[EventListener]
    public function handleUnserializeRequest(UnserializeValueRequest $request): void
    {
        $request->unserializedValue = $this->unserialize($request->value);
    }
}
