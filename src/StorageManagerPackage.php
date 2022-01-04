<?php

declare(strict_types=1);

namespace Medas\StorageManager;

use Medas\EntityManager\EntityManagerPackage;
use Medas\ServiceManager\BasePackage;

class StorageManagerPackage extends BasePackage
{
    public function dependencies(): array
    {
        return $this->dependenciesByClass([EntityManagerPackage::class]);
    }

    public function sourceDirectory(): string
    {
        return __DIR__;
    }
}
