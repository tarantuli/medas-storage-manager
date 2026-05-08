<?php

declare(strict_types=1);

namespace Medas\StorageManager\Shared;

class UnserializeValueRequest
{
    public mixed $unserializedValue;

    public function __construct(
        public readonly mixed $value,
    )
    {
    }
}
