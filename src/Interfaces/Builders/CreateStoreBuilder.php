<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces\Builders;

use Medas\StorageManager\{Interfaces\Storage, Structure\Blueprint, UnitOfWork\ActionSet};

interface CreateStoreBuilder
{
    public function build(Storage $storage, Blueprint $blueprint): ActionSet;
}
