<?php

declare(strict_types=1);

namespace Medas\Test\MockUps\Relations;

use Medas\EntityManager\Attributes\{Entity, Id, IsGeneratedValue, IsUnique};
use Medas\EntityManager\Types\{Integer, Text};

#[Entity(store: 'groups')]
class Group
{
    #[Id, Integer, IsGeneratedValue]
    private int $id;

    #[Text, IsUnique]
    private string $name;
}
