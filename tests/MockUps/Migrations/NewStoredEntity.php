<?php

declare(strict_types=1);

namespace Medas\Test\MockUps\Migrations;

use Medas\EntityManager\Attributes\{Entity, Id, IsGeneratedValue, IsNullable, IsUnique, Property};
use Medas\EntityManager\Types\DateTime;

#[Entity(store: 'new_stored_entities')]
class NewStoredEntity
{
    #[Property, IsUnique]
    public string $name;
    #[Property, IsNullable, DateTime]
    public ?\DateTime $createdAt;
    #[Id, IsGeneratedValue]
    private int $id;

    public function id(): int|null
    {
        return $this->id ?? null;
    }
}
