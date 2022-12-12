<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps\BackedEnums;

use Medas\EntityManager\Attributes\{Entity, Id, Property};

#[Entity(store: 'backed_enum_entities')]
class BackedEnumEntity
{
    #[Id]
    private IntBackedEnum $enum;

    #[Property]
    private StringBackedEnum $stringBackedEnum;
}
