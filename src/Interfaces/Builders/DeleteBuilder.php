<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces\Builders;

use Medas\StorageManager\Interfaces\Store;
use Medas\StorageManager\UnitOfWork\ActionSet;

interface DeleteBuilder
{
    public function build(Store $store, array $conditions): ActionSet;
}
