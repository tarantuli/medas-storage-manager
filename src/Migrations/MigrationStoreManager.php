<?php

declare(strict_types=1);

namespace Medas\StorageManager\Migrations;

use Medas\Core\Attributes\{ConfigValue, Service};
use Medas\StorageManager\ConfigOptions\MigrationsStoreName;
use Medas\StorageManager\Exceptions\MigrationStoreDoesNotExist;
use Medas\StorageManager\Interfaces\{Builders\MigrationStoreBuilder, Store};
use Medas\StorageManager\StorageManager;

#[Service]
readonly class MigrationStoreManager
{
    public function __construct(
        private StorageManager             $storageManager,

        #[ConfigValue(MigrationsStoreName::class)]
        private string                     $migrationsStoreName,
        private MigrationStoreBuilder|null $builder,
    )
    {
    }

    public function store(): Store
    {
        $storageController = $this->storageManager->controller();
        $store = $storageController->store($this->migrationsStoreName);

        if (!$storageController->hasStore($store)) {
            if ($this->builder === null) {
                throw new MigrationStoreDoesNotExist();
            }

            $this->builder->build($store);
        }

        return $store;
    }
}
