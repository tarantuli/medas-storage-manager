<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps\ManyToMany;

use Medas\Core\Interfaces\HasId;
use Medas\EntityManager\Attributes\{Entity, Id, IsGeneratedValue, Property};

#[Entity(store: 'books')]
class Book implements HasId
{
    #[Id, IsGeneratedValue]
    private int $id;

    #[Property]
    private Labels $labels;

    public function id(): int
    {
        return $this->id;
    }
}
