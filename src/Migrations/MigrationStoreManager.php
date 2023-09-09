<?php

declare(strict_types=1);

namespace Medas\StorageManager\Migrations;

use Medas\ConfigOptions\OptionController;
use Medas\Core\Attributes\Service;
use Medas\StorageManager\ConfigOptions\MigrationsStore;
use Medas\StorageManager\Interfaces\Store;
use Medas\StorageManager\StorageManager;
use Medas\StorageManager\Structure\{Blueprint, Blueprint\Field, Blueprint\Index, Blueprint\Type};

#[Service]
readonly class MigrationStoreManager
{
    private Store $store;

    public function __construct(
        private MigrationsStore  $migrationsStore,
        private OptionController $optionController,
        private StorageManager   $storageManager,
    )
    {
    }

    public function get(): Store
    {
        if (!isset($this->store)) {
            /** @var Store $store */
            $store = $this->optionController->getValue($this->migrationsStore);

            if (!$this->storageManager->controller($store->storage())->hasStore($store)) {
                $this->build($store);
            }

            $this->store = $store;
        }

        return $this->store;
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

        $storageController = $this->storageManager->controller($store->storage());

        $actions = $storageController->actionBuilders()->createStore()
            ->build($store->storage(), $blueprint);

        $storageController->actionExecutor()->executeSet($actions);
    }
}
