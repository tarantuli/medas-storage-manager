<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

interface StoreRecord
{
    public function data(): array;

    public function patch(array $values): void;
}
