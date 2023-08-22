<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

interface Record extends \ArrayAccess, \Iterator
{
    public function data(): array;

    public function patch(array $values): void;
}
