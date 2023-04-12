<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps\Migrations;

use Medas\EntityManager\Attributes\{Entity, Id, IsGeneratedValue, IsUnique, Property};
use Medas\EntityManager\Types\DateTime;
use Medas\Core\Interfaces\Guid;

#[Entity(store: 'stored_entities')]
class StoredEntity
{
    #[Id, IsGeneratedValue]
    private int $id;

    #[Property, IsUnique]
    public string $name;

    #[Property, DateTime]
    public ?\DateTime $createdAt;

    #[Property]
    public string $defaultString = 'default string';

    #[Property]
    public int $defaultInteger = 10;

    #[Property]
    public Guid $guid;

    public function id(): int|null
    {
        return $this->id ?? null;
    }
}
