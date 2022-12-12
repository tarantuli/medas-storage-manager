<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps\UnbackedEnums;

use Medas\EntityManager\Attributes\Entity;
use Medas\EntityManager\Attributes\Id;

#[Entity(store: 'unbacked_enum_entities')]
class UnbackedEnumEntity
{
    #[Id]
    private UnbackedEnum $enum;
}
