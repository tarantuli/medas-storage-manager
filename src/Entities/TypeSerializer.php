<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

interface TypeSerializer
{
    public function deserialize(mixed $value): mixed;

    public function serialize(mixed $value): mixed;
}
