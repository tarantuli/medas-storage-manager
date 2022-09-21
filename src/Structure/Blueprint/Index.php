<?php

declare(strict_types=1);

namespace Medas\StorageManager\Structure\Blueprint;

class Index
{
    /** @param Field[] $fields */
    public function __construct(
        public array $fields = [],
        public bool  $isPrimary = false,
        public bool  $isUnique = false,
    )
    {
    }
}
