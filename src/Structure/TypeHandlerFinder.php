<?php

declare(strict_types=1);

namespace Medas\StorageManager\Structure;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\Type;
use Medas\EntityManager\Types\{Binary, Boolean, Collection, DateTime, FloatingPoint, Integer, Relation, Text};
use Medas\StorageManager\Exceptions\UnhandledType;
use Medas\StorageManager\Exceptions\UnhandledTypeString;
use Medas\StorageManager\Structure\TypeHandlers\TypeHandler;

#[Service]
class TypeHandlerFinder
{
    public function __construct(
        private readonly TypeHandlers\BinaryHandler     $binaryHandler,
        private readonly TypeHandlers\BooleanHandler    $booleanHandler,
        private readonly TypeHandlers\CollectionHandler $collectionHandler,
        private readonly TypeHandlers\DateTimeHandler   $dateTimeHandler,
        private readonly TypeHandlers\IntegerHandler    $integerHandler,
        private readonly TypeHandlers\FloatHandler      $floatHandler,
        private readonly TypeHandlers\RelationHandler   $relationHandler,
        private readonly TypeHandlers\TextHandler       $textHandler,
    )
    {
    }

    public function for(Type $type): TypeHandler
    {
        // Order matters, don't sort by name
        return match (true) {
            $type instanceof Text => $this->textHandler,
            $type instanceof Binary => $this->binaryHandler,
            $type instanceof Boolean => $this->booleanHandler,
            $type instanceof Collection => $this->collectionHandler,
            $type instanceof DateTime => $this->dateTimeHandler,
            $type instanceof FloatingPoint => $this->floatHandler,
            $type instanceof Integer => $this->integerHandler,
            $type instanceof Relation => $this->relationHandler,
            default => throw new UnhandledType($type),
        };
    }

    public function forString(string $type): TypeHandler
    {
        return match (true) {
            $type === 'bool' => $this->booleanHandler,
            $type === 'int' => $this->integerHandler,
            class_exists($type) => $this->relationHandler,
            default => throw new UnhandledTypeString($type),
        };
    }
}
