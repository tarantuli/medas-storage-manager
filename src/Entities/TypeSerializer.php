<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\ServiceManager\Interfaces\Type;

interface TypeSerializer
{
    public function deserialize(Type $type, mixed $value): mixed;

    public function serialize(Type $type, mixed $value): mixed;
}
