<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Structure\TypeHandlers;

use Medas\EntityManager\MetaData\Property;
use Medas\EntityManager\Types\DateTime;
use Medas\ServiceManager\Attributes\Service;

#[Service]
class DateTimeHandler extends BaseHandler
{
    public function fieldDefinition(Property $property): string
    {
        /** @var DateTime $type */
        return 'datetime';
    }
}
