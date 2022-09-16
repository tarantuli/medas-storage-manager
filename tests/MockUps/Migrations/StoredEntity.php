<?php

declare(strict_types=1);

namespace Medas\Test\MockUps\Migrations;

use Medas\EntityManager\Attributes\{Entity, Id, IsGeneratedValue, IsNullable, IsUnique, Property};
use Medas\EntityManager\Types\DateTime;

#[Entity(store: 'stored_entities')]
class StoredEntity
{
    #[Id, IsGeneratedValue]
    private int $id;

    #[Property, IsUnique]
    public string $name;

    #[Property, IsNullable, DateTime]
    public ?\DateTime $createdAt;

    #[Property]
    public string $defaultString = 'default string';

    #[Property]
    public int $defaultInteger = 10;

    public function id(): int|null
    {
        return $this->id ?? null;
    }
}
