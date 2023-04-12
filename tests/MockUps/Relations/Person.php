<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps\Relations;

use Medas\EntityManager\Attributes\{Entity, Id, IsGeneratedValue, IsUnique, Property};
use Medas\Core\Interfaces\HasId;

#[Entity(store: 'people')]
class Person implements HasId
{
    #[Id, IsGeneratedValue]
    private int $id;

    #[Property, IsUnique]
    private string $name;

    #[Property]
    private Group $group;

    public function id(): int
    {
        return $this->id;
    }

    public function group(): Group
    {
        return $this->group;
    }
}
