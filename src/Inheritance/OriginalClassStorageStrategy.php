<?php

declare(strict_types=1);

namespace Medas\StorageManager\Inheritance;

use Medas\StorageManager\Interfaces\Storage;
use Medas\StorageManager\Structure\Blueprint;
use Medas\StorageManager\UnitOfWork\ActionSet;

interface OriginalClassStorageStrategy
{
    public function buildStoreActions(Blueprint $blueprint, Storage $storage): ActionSet;

    public function createValuesToStore(Blueprint $blueprint, object $entity): array;

    public function getOriginalClass(Blueprint $blueprint, mixed $id): string;
}
