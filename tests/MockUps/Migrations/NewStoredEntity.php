<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps\Migrations;

use Medas\EntityManager\Attributes\{Entity, Id, IsGeneratedValue, IsNullable, IsUnique, Property};
use Medas\EntityManager\Types\DateTime;

#[Entity(store: 'new_stored_entities')]
class NewStoredEntity
{
    #[Id, IsGeneratedValue]
    private int $id;

    #[Property, IsUnique]
    public string $name;

    #[Property, IsNullable, DateTime]
    public ?\DateTime $createdAt;

    public function id(): int|null
    {
        return $this->id ?? null;
    }
}
