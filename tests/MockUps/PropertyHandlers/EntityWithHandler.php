<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps\PropertyHandlers;

use Medas\EntityManager\Attributes\{Entity, Id, Property};
use Medas\EntityManager\Properties\SerializingHandler;
use Medas\ServiceManager\Interfaces\Guid;

#[Entity(store: 'entities_with_handler')]
class EntityWithHandler
{
    #[Id]
    public Guid $guid;

    #[Property(handler: SerializingHandler::class)]
    public PropertyClass $propertyClass;
}
