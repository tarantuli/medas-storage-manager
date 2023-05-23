<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps\Attributes;

use Medas\Core\Interfaces\HasId;
use Medas\EntityManager\Attributes\{Entity, Id, IsGeneratedValue};
use Medas\EntityManager\Traits\Timestamps;

#[Entity(store: 'timestamped_posts')]
class TimestampedPost implements HasId
{
    use Timestamps;

    #[Id, IsGeneratedValue]
    private int $id;

    public int $counter = 0;

    public function id(): int
    {
        return $this->id;
    }
}
