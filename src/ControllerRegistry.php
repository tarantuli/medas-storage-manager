<?php

declare(strict_types=1);

namespace Medas\StorageManager;

use Medas\Core\{Attributes\Service, Interfaces\CacheManager, Interfaces\ImplementorFinder};

#[Service]
readonly class ControllerRegistry
{
    public function __construct(
        private CacheManager      $cacheManager,
        private ImplementorFinder $implementorFinder,
    )
    {
    }

    /** @return Interfaces\StorageController[] */
    public function all(): array
    {
        return $this->cacheManager->get()->get(__CLASS__, fn() => $this->discoverAll());
    }

    /** @return Interfaces\StorageController[] */
    private function discoverAll(): array
    {
        return namesToServices($this->implementorFinder->find(Interfaces\StorageController::class));
    }
}
