<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces\Builders;

use Medas\EntityManager\Selector\{Slice, Sorting\SortBy};
use Medas\StorageManager\{Interfaces\Store, UnitOfWork\ActionSet};

interface UpdateBuilder
{
    /**
     * @param SortBy[] $sorts
     */
    public function build(
        Store      $store,
        array      $updates,
        array      $conditions,
        array      $sorts = [],
        Slice|null $slice = null
    ): ActionSet;
}
