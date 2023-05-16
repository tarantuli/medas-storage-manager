<?php

declare(strict_types=1);

namespace Medas\StorageManager\Structure\TypeHandlers;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\MetaData\Property;
use Medas\StorageManager\Structure\Blueprint\Type;

#[Service]
class CollectionHandler extends BaseHandler
{
    public function fieldType(Property|null $property): Type
    {
        return Type::Collection;
    }
}
