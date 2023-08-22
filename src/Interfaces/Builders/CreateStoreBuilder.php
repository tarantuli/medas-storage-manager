<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces\Builders;

use Medas\StorageManager\Structure\Blueprint;
use Medas\StorageManager\UnitOfWork\ActionSet;

interface CreateStoreBuilder
{
    public function build(Blueprint $blueprint): ActionSet;
}
