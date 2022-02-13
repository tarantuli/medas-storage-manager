<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities\Exceptions;

use Medas\Core\Exceptions\BaseException;
use Medas\StorageManager\Interfaces\Store;

class StoreDoesNotHavePropertyException extends BaseException
{
    public function __construct(Store $store, string $property)
    {
        parent::__construct($store->storage()->name(), $store->name(), $property);
    }

    public function pattern(): string
    {
        return 'store %s:%s does not have a property named %s';
    }
}
