<?php

declare(strict_types=1);

namespace Medas\StorageManager;

use Medas\Cache\CachePackage;
use Medas\Core\{AsSingleton, BasePackage};
use Medas\EntityManager\EntityManagerPackage;

class StorageManagerPackage extends BasePackage
{
    use AsSingleton;

    public function dependencies(): array
    {
        return [
            CachePackage::instance(),
            EntityManagerPackage::instance(),
        ];
    }

    public function sourceDirectory(): string
    {
        return __DIR__;
    }
}
