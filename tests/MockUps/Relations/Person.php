<?php

declare(strict_types=1);

namespace Medas\Test\MockUps\Relations;

use Medas\EntityManager\Attributes\{Entity, Id, IsGeneratedValue, IsUnique};
use Medas\EntityManager\Types\{Integer, Relation, Text};

#[Entity(store: 'persons')]
class Person
{
    #[Id, Integer, IsGeneratedValue]
    private int $id;

    #[Text, IsUnique]
    private string $name;

    #[Relation(entity: Group::class)]
    private Group $group;
}
