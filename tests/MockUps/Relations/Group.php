<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps\Relations;

use Medas\EntityManager\Attributes\{Entity, HasId, Id, IsGeneratedValue, IsUnique, Property};

#[Entity(store: 'groups')]
class Group implements HasId
{
    #[Id, IsGeneratedValue]
    private int $id;

    #[Property, IsUnique]
    private string $name;

    public function id(): int
    {
        return $this->id;
    }
}
