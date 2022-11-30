<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps\Attributes;

use Medas\EntityManager\Attributes\{Entity, HasId, Id, IsGeneratedValue, Property};
use Medas\EntityManager\Traits\Timestamps;

#[Entity(store: 'timestamped_posts')]
class TimestampedPost implements HasId
{
    use Timestamps;

    #[Id, IsGeneratedValue]
    private int $id;

    #[Property]
    public int $counter = 0;

    public function id(): int
    {
        return $this->id;
    }
}
