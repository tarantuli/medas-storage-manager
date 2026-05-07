<?php

declare(strict_types=1);

namespace Medas\StorageManager\Inheritance;

use Medas\EntityManager\MetaData;
use Medas\StorageManager\Interfaces\Storage;

interface OriginalClassStorageStrategy
{
    public function createValuesToStore(MetaData $metaData, object $entity): array;

    public function getOriginalClass(MetaData $metaData, Storage $storage, mixed $id): string;
}
