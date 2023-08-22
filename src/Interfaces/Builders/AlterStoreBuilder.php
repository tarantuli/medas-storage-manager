<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces\Builders;

use Medas\StorageManager\Structure\{Blueprint, Changes\Changes};
use Medas\StorageManager\UnitOfWork\ActionSet;

interface AlterStoreBuilder
{
    public function build(Blueprint $blueprint, Changes $changes): ActionSet;
}
