<?php

declare(strict_types=1);

namespace Medas\StorageManager\Shared;

class SerializeValueRequest
{
    public mixed $serializedValue;

    public function __construct(
        public readonly mixed $value,
    )
    {
    }
}
