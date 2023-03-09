<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps\Attributes;

use Medas\EntityManager\Attributes\{Entity, Id, IsGeneratedValue, Property};
use Medas\EntityManager\Traits\Timestamps;
use Medas\ServiceManager\Interfaces\HasId;

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
