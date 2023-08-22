<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces\Builders;

use Medas\Core\Interfaces\ManagedCollection;
use Medas\EntityManager\Types\Collection;
use Medas\StorageManager\Interfaces\Store;
use Medas\StorageManager\UnitOfWork\ActionSet;

interface CollectionUpdateBuilder
{
    public function build(Store $store, object $entity, string $name, Collection $type, ManagedCollection $values): ActionSet;
}
