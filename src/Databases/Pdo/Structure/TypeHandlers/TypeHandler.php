<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Structure\TypeHandlers;

use Medas\EntityManager\MetaData\Property;
use Medas\StorageManager\Databases\Pdo\Structure\Blueprint\ForeignKey;
use Medas\StorageManager\Entities\TypeSerializer;

interface TypeHandler extends TypeSerializer
{
    public function fieldDefinition(Property $property): string;

    public function foreignKey(Property $property): ForeignKey|null;
}
