<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Structure\Blueprint;

class Index
{
    /** @var Field[] */
    public array $fields;

    public bool $isUnique = false;

    public function __construct(
        public string $name
    )
    {
    }
}
