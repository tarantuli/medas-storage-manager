<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps\Relations;

use Medas\Core\Interfaces\HasId;
use Medas\EntityManager\Attributes\{Entity, Id, IsGeneratedValue, IsUnique};

#[Entity(store: 'people')]
class Person implements HasId
{
    #[Id, IsGeneratedValue]
    private int $id;

    #[IsUnique]
    private string $name;

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
