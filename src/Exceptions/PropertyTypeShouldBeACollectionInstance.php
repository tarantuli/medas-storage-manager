<?php

declare(strict_types=1);

namespace Medas\StorageManager\Exceptions;

use Medas\Core\Exceptions\BaseException;
use Medas\EntityManager\MetaData\Property;

class PropertyTypeShouldBeACollectionInstance extends BaseException
{
    public function __construct(Property $property)
    {
        parent::__construct($property->name, get_debug_type($property->type));
    }

    public function pattern(): string
    {
        return 'property %s should be a collection instance, found %s instead';
    }
}
