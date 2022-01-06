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
}
