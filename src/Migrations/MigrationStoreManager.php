<?php

declare(strict_types=1);

namespace Medas\StorageManager\Migrations;

use Medas\ConfigManager\ConfigManager;
use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\ConfigOptions\MigrationsStore;
use Medas\StorageManager\Databases\Pdo\Structure\Blueprint;
use Medas\StorageManager\Interfaces\Store;

#[Service]
class MigrationStoreManager
{
    public function __construct(
        private ConfigManager $configManager,
    )
    {
    }

    public function get(): Store
    {
        /** @var Store $store */
        $store = $this->configManager->getOptionValue(MigrationsStore::instance());

        if (!$store->exists()) {
            $this->build($store);
        }

        return $store;
    }

    private function build(Store $store): void
    {
        $blueprint = new Blueprint();

        $blueprint->name = $store->name();

        $migrationField = new Blueprint\Field('migration', 'varchar(255) not null');
        $datetimeField = new Blueprint\Field('migrated_at', 'datetime not null');
        $index = new Blueprint\Index('migration');

        $blueprint->addField($migrationField);
        $blueprint->addField($datetimeField);

        $index->fields[] = $migrationField;
        $blueprint->addIndex($index);

        $store->storage()->queryBuilder()->createTable($blueprint)->execute();
    }
}
