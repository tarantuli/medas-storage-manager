<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps\ManyToMany;

use Medas\Core\Interfaces\HasId;
use Medas\EntityManager\Attributes\Entity;
use Medas\EntityManager\Attributes\Id;
use Medas\EntityManager\Attributes\IsGeneratedValue;
use Medas\EntityManager\Attributes\IsUnique;
use Medas\EntityManager\Attributes\Property;

#[Entity(store: 'labels')]
class Label implements HasId
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
