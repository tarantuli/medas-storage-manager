<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces\Fetchers;

use Medas\StorageManager\Interfaces\Record;
use Medas\StorageManager\Interfaces\RecordSet;
use Medas\StorageManager\Interfaces\Storage;
use Medas\StorageManager\Interfaces\Store;

interface FilteredFetcher
{
    public function fetch(Store $store, array $filters, Storage $storage = null): RecordSet;

    public function fetchOne(Store $store, array $filters, Storage $storage = null): Record|null;
}
