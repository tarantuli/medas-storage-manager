<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps\Relations;

use Medas\EntityManager\Attributes\Entity;
use Medas\EntityManager\Attributes\HasId;
use Medas\EntityManager\Attributes\Id;
use Medas\EntityManager\Attributes\IsGeneratedValue;
use Medas\EntityManager\Attributes\IsUnique;
use Medas\EntityManager\Attributes\Property;

#[Entity(store: 'other_people')]
class AnEntitySortingBeforeGroup implements HasId
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
