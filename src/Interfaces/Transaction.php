<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

interface Transaction
{
    public function begin(): void;

    public function rollback(): void;

    public function commit(): void;
}
