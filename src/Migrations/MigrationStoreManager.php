<?php

declare(strict_types=1);

namespace Medas\StorageManager\Migrations;

use Medas\Core\Attributes\{ConfigValue, Service};
use Medas\StorageManager\ConfigOptions\MigrationsStoreName;
use Medas\StorageManager\Interfaces\Store;
use Medas\StorageManager\StorageManager;
use Medas\StorageManager\Structure\{Blueprint, Blueprint\Field, Blueprint\Index, Blueprint\Type};

#[Service]
readonly class MigrationStoreManager
{
    public Store $store;

    public function __construct(
        private StorageManager $storageManager,

        #[ConfigValue(MigrationsStoreName::class)]
        private string         $migrationsStoreName,
    )
    {
        $storageController = $this->storageManager->controller();
        $store = $storageController->store($this->migrationsStoreName);

        if (!$storageController->hasStore($store)) {
            $this->build($store);
        }

        $this->store = $store;
    }

    private function build(Store $store): void
    {
        $blueprint = new Blueprint();

        $blueprint->name = $store->name();
        $migrationField = new Field('migration', Type::Text);
        $datetimeField = new Field('migratedAt', Type::DateTime);

        $blueprint->addField($migrationField);
        $blueprint->addField($datetimeField);
        $blueprint->addIndex(new Index([$migrationField]));

        $storageController = $this->storageManager->controller();
        $actions = $storageController->actionBuilders()->createStore()
            ->build($store->storage(), $blueprint);

        $storageController->actionExecutor()->executeSet($actions);
    }
}
