<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps\Migrations;

use Medas\EntityManager\Attributes\{Entity, Id, IsGeneratedValue, IsUnique};
use Medas\EntityManager\Types\DateTime;

#[Entity(store: 'new_stored_entities')]
class NewStoredEntity
{
    #[Id, IsGeneratedValue]
    private int $id;

    #[IsUnique]
    public string $name;

    #[DateTime]
    public \DateTime|null $createdAt;

    public function id(): int|null
    {
        return $this->id ?? null;
    }
}
