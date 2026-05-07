<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

use Medas\StorageManager\Type;

interface FieldMetaData
{
    public function name(): string;

    public function type(): Type;

    public function isNullable(): bool;

    public function length(): int|null;

    public function precision(): int|null;
}
