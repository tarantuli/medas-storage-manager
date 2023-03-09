<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps\Attributes;

use Medas\EntityManager\Attributes\{Entity, Id, IsGeneratedValue, Property};
use Medas\ServiceManager\Interfaces\{Guid, HasId};

#[Entity(store: 'guid_property_posts')]
class GuidPropertyPost implements HasId
{
    #[Id, IsGeneratedValue]
    private int $id;

    #[Property]
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
