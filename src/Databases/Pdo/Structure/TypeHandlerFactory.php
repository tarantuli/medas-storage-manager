<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Structure;

use Medas\EntityManager\Types\{Binary, DateTime, Integer, Relation, Text};
use Medas\EntityManager\Types\Type;
use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\Databases\Pdo\Exceptions\UnhandledTypeException;
use Medas\StorageManager\Databases\Pdo\Structure\TypeHandlers\{BinaryHandler,
    DateTimeHandler,
    IntegerHandler,
    RelationHandler,
    TextHandler,
    TypeHandler
};

#[Service]
class TypeHandlerFactory
{
    public function __construct(
        private BinaryHandler   $binaryHandler,
        private DateTimeHandler $dateTimeHandler,
        private IntegerHandler  $integerHandler,
        private RelationHandler $relationHandler,
        private TextHandler     $textHandler,
    )
    {
    }

    public function for(Type $type): TypeHandler
    {
        return match (true) {
            $type instanceof Binary => $this->binaryHandler,
            $type instanceof DateTime => $this->dateTimeHandler,
            $type instanceof Integer => $this->integerHandler,
            $type instanceof Relation => $this->relationHandler,
            $type instanceof Text => $this->textHandler,
            default => throw new UnhandledTypeException($type),
        };
    }
}
