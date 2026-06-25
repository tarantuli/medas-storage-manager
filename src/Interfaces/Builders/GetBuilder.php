<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces\Builders;

use Medas\StorageManager\{Interfaces\Store, UnitOfWork\ActionSet};

interface GetBuilder
{
    /**
     * @param Store[] $stores
     */
    public function build(array $stores, array $filters): ActionSet;
}
