<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps\Inheritence;

use Medas\Core\Interfaces\HasId;
use Medas\EntityManager\Attributes\{Entity, Id, IsGeneratedValue, IsUnique};

#[Entity('i_cards')]
class Card implements HasId
{
    #[Id, IsGeneratedValue]
    protected int $id;

    #[IsUnique]
    protected string $name;

    public function id(): int
    {
        return $this->id;
    }
}
