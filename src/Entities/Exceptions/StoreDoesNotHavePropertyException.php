<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities\Exceptions;

use Medas\Core\Exceptions\BaseException;
use Medas\StorageManager\Interfaces\Store;
use Medas\StorageManager\StorageManager;

class StoreDoesNotHavePropertyException extends BaseException
{
    public function __construct(Store $store, string $property)
    {
        parent::__construct(
            service(StorageManager::class)->getName($store->storage()),
            $store->name(),
            $property
        );
    }

    public function pattern(): string
    {
        return 'store %s:%s does not have a property named %s';
    }
}
