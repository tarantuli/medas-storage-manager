<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces\Builders;

use Medas\StorageManager\Interfaces\Store;

interface MigrationStoreBuilder
{
    public function build(Store $store): void;
}
