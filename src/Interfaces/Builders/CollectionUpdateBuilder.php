<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces\Builders;

use Medas\Core\{Interfaces\ManagedCollection, Types\Collection};
use Medas\StorageManager\{Interfaces\Store, UnitOfWork\ActionSet};

interface CollectionUpdateBuilder
{
    public function build(
        Store             $store,
        object            $entity,
        string            $name,
        Collection        $type,
        ManagedCollection $values
    ): ActionSet;
}
