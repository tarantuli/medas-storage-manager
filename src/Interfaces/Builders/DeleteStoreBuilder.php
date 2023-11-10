<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces\Builders;

use Medas\StorageManager\{Interfaces\Store, UnitOfWork\ActionSet};

interface DeleteStoreBuilder
{
    public function build(Store $store): ActionSet;
}
