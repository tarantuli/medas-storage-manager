<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps\PropertyHandlers;

use Medas\Core\Interfaces\Guid;
use Medas\EntityManager\Attributes\{Entity, Handler, Id};
use Medas\EntityManager\Properties\SerializingHandler;

#[Entity(store: 'entities_with_handler')]
class EntityWithHandler
{
    #[Id]
    public Guid $guid;

    #[Handler(SerializingHandler::class)]
    public PropertyClass $propertyClass;
}
