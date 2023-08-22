<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces\Fetchers;

use Medas\EntityManager\MetaData\Property;
use Medas\StorageManager\Interfaces\Storage;
use Medas\StorageManager\Interfaces\Store;

interface CollectionRecordFetcher
{
    public function fetch(Store $store, object $entity, Property $property, Storage $storage = null): iterable;
}
