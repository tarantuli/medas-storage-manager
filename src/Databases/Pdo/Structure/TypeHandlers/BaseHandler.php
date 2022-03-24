<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Structure\TypeHandlers;

use Medas\EntityManager\MetaData\Property;
use Medas\StorageManager\Databases\Pdo\Structure\Blueprint\ForeignKey;

abstract class BaseHandler implements TypeHandler
{
    public function foreignKey(Property $property): ForeignKey|null
    {
        return null;
    }

    public function deserialize(mixed $value): mixed
    {
        return $value;
    }

    public function serialize(mixed $value): mixed
    {
        return $value;
    }
}
