<?php

declare(strict_types=1);

namespace Medas\Test\MockUps;

use Medas\EntityManager\Attributes\{Entity, Id, IsGeneratedValue, IsNullable, IsUnique};
use Medas\EntityManager\Types as Type;

#[Entity(store: 'stored_entities')]
class StoredEntity
{
    #[Id]
    #[IsGeneratedValue]
    #[Type\Integer]
    private int $id;

    #[Type\Text]
    #[IsUnique]
    public string $name;

    #[Type\DateTime]
    #[IsNullable]
    public ?\DateTime $createdAt;

    public function id(): int|null
    {
        return $this->id ?? null;
    }
}
