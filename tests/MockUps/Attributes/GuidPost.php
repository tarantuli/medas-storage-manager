<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps\Attributes;

use Medas\EntityManager\Attributes\{Entity, HasId, Id};
use Medas\EntityManager\Types\Guid;

#[Entity(store: 'guid_posts')]
class GuidPost implements HasId
{
    #[Id, Guid]
    private string $id;

    public function id(): string
    {
        return $this->id;
    }
}
