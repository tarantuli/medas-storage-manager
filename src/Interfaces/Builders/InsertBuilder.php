<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces\Builders;

use Medas\StorageManager\{Interfaces\Store, UnitOfWork\ActionSet, UnitOfWork\Priority};

interface InsertBuilder
{
    public function build(Store $store, array $values, Priority $priority = Priority::CreateRecord): ActionSet;
}
