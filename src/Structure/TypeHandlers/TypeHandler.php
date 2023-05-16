<?php

declare(strict_types=1);

namespace Medas\StorageManager\Structure\TypeHandlers;

use Medas\EntityManager\MetaData\Property;
use Medas\StorageManager\Structure\Blueprint\{ForeignKey, Type};

interface TypeHandler
{
    public function foreignKey(Property $property): ForeignKey|null;

    public function fieldType(Property|null $property): Type;
}
