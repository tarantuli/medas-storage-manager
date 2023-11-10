<?php

declare(strict_types=1);

namespace Medas\StorageManager\Inheritance;

use Medas\StorageManager\{Interfaces\Storage, Structure\Blueprint, UnitOfWork\ActionSet};

interface OriginalClassStorageStrategy
{
    public function buildStoreActions(Blueprint $blueprint, Storage $storage): ActionSet;

    public function createValuesToStore(Blueprint $blueprint, object $entity): array;

    public function getOriginalClass(Blueprint $blueprint, mixed $id): string;
}
