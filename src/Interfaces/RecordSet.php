<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

interface RecordSet
{
    public function fetchRecords(): array;

    public function fetchRecord(): StoreRecord|null;

    public function hasRecords(): bool;
}
