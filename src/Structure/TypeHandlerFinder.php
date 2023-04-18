<?php

declare(strict_types=1);

namespace Medas\StorageManager\Structure;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\Type;
use Medas\EntityManager\Types\{Binary, Boolean, DateTime, FloatingPoint, Integer, Relation, Text};
use Medas\StorageManager\Exceptions\UnhandledType;
use Medas\StorageManager\Structure\TypeHandlers\TypeHandler;

#[Service]
class TypeHandlerFinder
{
    public function __construct(
        private readonly TypeHandlers\BinaryHandler   $binaryHandler,
        private readonly TypeHandlers\BooleanHandler  $booleanHandler,
        private readonly TypeHandlers\DateTimeHandler $dateTimeHandler,
        private readonly TypeHandlers\IntegerHandler  $integerHandler,
        private readonly TypeHandlers\FloatHandler    $floatHandler,
        private readonly TypeHandlers\RelationHandler $relationHandler,
        private readonly TypeHandlers\TextHandler     $textHandler,
    )
    {
    }

    public function for(Type $type): TypeHandler
    {
        return match (true) {
            $type instanceof Text => $this->textHandler,
            $type instanceof Binary => $this->binaryHandler,
            $type instanceof DateTime => $this->dateTimeHandler,
            $type instanceof Relation => $this->relationHandler,
            $type instanceof Integer => $this->integerHandler,
            $type instanceof Boolean => $this->booleanHandler,
            $type instanceof FloatingPoint => $this->floatHandler,
            default => throw new UnhandledType($type),
        };
    }
}
