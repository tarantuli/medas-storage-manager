<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Structure\TypeHandlers;

use Medas\EntityManager\MetaData\Property;

interface TypeHandler
{
    public function fieldType(Property $property): string;
}
