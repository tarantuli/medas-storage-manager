<?php

declare(strict_types=1);

namespace Medas\StorageManager\Structure\TypeHandlers;

use Medas\EntityManager\MetaData\Property;
use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\Structure\Blueprint\Type;

#[Service]
class FloatHandler extends BaseHandler
{
    public function fieldType(Property $property): Type
    {
        return Type::Float;
    }
}
