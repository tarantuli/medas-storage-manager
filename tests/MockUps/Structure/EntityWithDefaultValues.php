<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps\Structure;

use Medas\EntityManager\Attributes\{Entity, Id};
use Medas\EntityManager\Types\Guid;

#[Entity]
class EntityWithDefaultValues
{
    #[Id, Guid]
    public string $id;

    private \DateTime $dateTimeNotNullNoDefault;

    private \DateTime|null $dateTimeNullNoDefault;

    private \DateTime|null $dateTimeNullDefaultNull = null;
}
