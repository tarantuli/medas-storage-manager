<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps\Structure;

use Medas\EntityManager\Attributes\{Entity, Id, Property};
use Medas\EntityManager\Types\Guid;

#[Entity]
class EntityWithDefaultValues
{
    #[Id, Guid]
    public string $id;

    #[Property]
    private \DateTime $dateTimeNotNullNoDefault;

    #[Property]
    private \DateTime|null $dateTimeNullNoDefault;

    #[Property]
    private \DateTime|null $dateTimeNullDefaultNull = null;
}
