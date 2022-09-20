<?php

declare(strict_types=1);

namespace Medas\StorageManager\Migrations;

use Medas\ConfigOptions\OptionController;
use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\ConfigOptions\MigrationsStore;
use Medas\StorageManager\Interfaces\Store;

#[Service]
class MigrationStoreManager
{
    private Store $store;

    public function __construct(
        private readonly OptionController $optionController,
    )
    {
    }

    public function get(): Store
    {
        if (!isset($this->store)) {
            $this->store = $this->optionController->getValue(MigrationsStore::instance());

            if (!$this->store->exists()) {
                $this->build($this->store);
            }
        }

        return $this->store;
    }

    private function build(Store $store): void
    {
        $blueprint = new Blueprint();

        $blueprint->name = $store->name();

        $migrationField = new Field('migration', 'varchar(255) not null');
        $datetimeField = new Field('migrated_at', 'datetime not null');
        $index = new Blueprint\Index('migration');

        $blueprint->addField($migrationField);
        $blueprint->addField($datetimeField);

        $index->fields[] = $migrationField;
        $blueprint->addIndex($index);

        (new CreateTableBuilder($blueprint))->create($store->storage())->execute();
    }
}
