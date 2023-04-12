<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps\Relations;

use Medas\EntityManager\Attributes\{Entity, Id, IsGeneratedValue, IsUnique, Property};
use Medas\Core\Interfaces\HasId;

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
