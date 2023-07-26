<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities\Exceptions;

use Medas\Core\Exceptions\BaseException;
use Medas\StorageManager\{Interfaces\Store, StorageManager};

class StoresDontHaveProperty extends BaseException
{
    /** @param Store[] $stores */
    public function __construct(array $stores, string $property)
    {
        $storage = null;
        $storeNames = [];

        foreach ($stores as $store) {
            if ($storage === null) {
                $storage = service(StorageManager::class)->getName($store->storage());
            }

            $storeNames[] = $store->name();
        }

        parent::__construct(
            count($storeNames) === 1 ? '' : 's',
            implode(', ', $storeNames),
            $storage,
            $property
        );
    }

    public function pattern(): string
    {
        return 'store%s %s in storage %s do not have a property named %s';
    }
}
