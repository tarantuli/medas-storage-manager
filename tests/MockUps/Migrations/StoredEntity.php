<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps\Migrations;

use Medas\Core\Interfaces\Guid;
use Medas\EntityManager\Attributes\{Entity, Id, IsGeneratedValue, IsUnique};
use Medas\EntityManager\Types\DateTime;

#[Entity(store: 'stored_entities')]
class StoredEntity
{
    #[Id, IsGeneratedValue]
    private int $id;

    #[IsUnique]
    public string $name;

    #[DateTime]
    public \DateTime|null $createdAt;

    public string $defaultString = 'default string';

    public int $defaultInteger = 10;

    public Guid $guid;

    public function id(): int|null
    {
        return $this->id ?? null;
    }
}
