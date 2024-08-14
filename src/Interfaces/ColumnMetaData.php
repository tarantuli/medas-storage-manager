<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

use Medas\Core\Interfaces\Type;

interface ColumnMetaData
{
    public function type(): Type;

    public function length(): int|null;

    public function precision(): int|null;
}
