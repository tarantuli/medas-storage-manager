<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\EntityManager\Types\Type;

interface TypeSerializerFinder
{
    public function for(Type $type): TypeSerializer;
}
