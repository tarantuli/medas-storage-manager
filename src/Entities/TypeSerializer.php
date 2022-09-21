<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\EntityManager\Types\Type;

interface TypeSerializer
{
    public function deserialize(Type $type, mixed $value): mixed;

    public function serialize(Type $type, mixed $value): mixed;
}
