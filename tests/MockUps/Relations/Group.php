<?php

declare(strict_types=1);

namespace Medas\Test\MockUps\Relations;

use Medas\EntityManager\Attributes\{Entity, Id, IsGeneratedValue, IsUnique, Property};

#[Entity(store: 'groups')]
class Group
{
    #[Id, IsGeneratedValue]
    private int $id;

    #[Property, IsUnique]
    private string $name;
}
