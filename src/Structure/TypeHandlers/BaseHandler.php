<?php

declare(strict_types=1);

namespace Medas\StorageManager\Structure\TypeHandlers;

use Medas\EntityManager\MetaData\Property;
use Medas\StorageManager\Structure\Blueprint\ForeignKey;

readonly abstract class BaseHandler implements TypeHandler
{
    public function foreignKey(Property $property): ForeignKey|null
    {
        return null;
    }
}
