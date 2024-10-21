<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

interface RecordSet
{
    public function fetchRecords(): array;

    public function fetchRecord(): Record|null;

    public function hasRecords(): bool;

    public function fetchMetaData(): RecordSetMetaData;
}
