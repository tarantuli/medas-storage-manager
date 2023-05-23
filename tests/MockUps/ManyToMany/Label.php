<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps\ManyToMany;

use Medas\Core\Interfaces\HasId;
use Medas\EntityManager\Attributes\Entity;
use Medas\EntityManager\Attributes\Id;
use Medas\EntityManager\Attributes\IsGeneratedValue;
use Medas\EntityManager\Attributes\IsUnique;

#[Entity(store: 'labels')]
class Label implements HasId
{
    #[Id, IsGeneratedValue]
    private int $id;

    #[IsUnique]
    private string $name;

    public function id(): int
    {
        return $this->id;
    }
}
