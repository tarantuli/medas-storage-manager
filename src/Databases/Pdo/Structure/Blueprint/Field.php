<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Structure\Blueprint;

class Field
{
    public function __construct(
        public string $name,
        public string $definition,
        public bool   $hasDefault = false,
        public mixed  $default = null,
    )
    {
    }
}
