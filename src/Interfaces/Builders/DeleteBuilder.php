<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces\Builders;

use Medas\StorageManager\Interfaces\Store;
use Medas\StorageManager\UnitOfWork\{ActionSet, Priority};

interface DeleteBuilder
{
    public function build(Store $store, array $conditions, Priority $priority = Priority::DeleteRecord): ActionSet;
}
