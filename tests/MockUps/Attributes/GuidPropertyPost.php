<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps\Attributes;

use Medas\Core\Interfaces\{Guid, HasId};
use Medas\EntityManager\Attributes\{Entity, Id, IsGeneratedValue};

#[Entity(store: 'guid_property_posts')]
class GuidPropertyPost implements HasId
{
    #[Id, IsGeneratedValue]
    private int $id;

    private Guid $guid;

    public function id(): int
    {
        return $this->id;
    }

    public function guid(): Guid
    {
        return $this->guid;
    }
}
