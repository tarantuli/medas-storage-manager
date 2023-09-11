<?php

declare(strict_types=1);

namespace Medas\StorageManager\Structure;


use Medas\StorageManager\Interfaces\Storage;
use Medas\StorageManager\UnitOfWork\ActionSet;

interface OriginalEntityClassStorageStrategy
{
    public function buildStoreActions(Storage $storage, Blueprint $blueprint): ActionSet;

    public function createValuesToStore(object $entity, Blueprint $blueprint): array;
}
