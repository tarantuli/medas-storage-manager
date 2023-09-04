<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces\Fetchers;

use Medas\StorageManager\Interfaces\{Record, RecordSet, Store};

interface FilteredFetcher
{
    public function fetch(Store $store, array $filters = []): RecordSet;

    public function fetchOne(Store $store, array $filters = []): Record|null;
}
