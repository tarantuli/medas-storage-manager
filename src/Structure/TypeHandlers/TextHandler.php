<?php

declare(strict_types=1);

namespace Medas\StorageManager\Structure\TypeHandlers;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\MetaData\Property;
use Medas\StorageManager\Structure\Blueprint\Type;

#[Service]
class TextHandler extends BaseHandler
{
    public function fieldType(Property $property): Type
    {
        return Type::Text;
    }
}
