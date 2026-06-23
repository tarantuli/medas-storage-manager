<?php

declare(strict_types=1);

namespace Medas\StorageManager\Migrations;

use Medas\Core\Attributes\{ConfigValue, Service};
use Medas\StorageManager\{ConfigOptions\MigrationsStoreName, Interfaces\Store, StorageManager};

#[Service]
readonly class MigrationStoreManager
{
    public function __construct(
        private StorageManager $storageManager,

        #[ConfigValue(MigrationsStoreName::class)]
        private string         $migrationsStoreName,
    )
    {
    }

    public function store(): Store
    {
        $storageController = $this->storageManager->controller();

        return $storageController->store($this->migrationsStoreName);
    }

    public function storeExists(): bool
    {
        return $this->storageManager->controller()->hasStore($this->store());
    }
}
